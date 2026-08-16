<?php

namespace Tests\Unit\ActiveGenerationBootstrap;

use App\Application\ActiveGenerationBootstrap\ActiveGenerationBootstrapState;
use App\Application\ActiveGenerationBootstrap\BootstrapActiveProjectionGenerationCommand;
use App\Application\ActiveGenerationBootstrap\BootstrapActiveProjectionGenerationStatus;
use App\Application\ActiveGenerationBootstrap\Contract\ActiveGenerationBootstrapStateReader;
use App\Application\ActiveGenerationBootstrap\DeterministicActiveProjectionGenerationBootstrapV1;
use App\Application\ActiveGenerationReader\ActiveGenerationReadResult;
use App\Application\ActiveGenerationReader\Contract\ActiveGenerationReader;
use App\Application\ProjectionRebuildRuntimeSource\CandidateBuildResult;
use App\Application\ProjectionRebuildRuntimeSource\Contract\InspectablePublicProjectionCandidateFactory;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionGenerationManager;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionGenerationValidator;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionRebuildEnumerator;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifest;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationTransition;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationValidation;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuilder;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuildPage;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuildScope;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGeneration;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionGenerationState;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Support\PublicListingReadModelFixture;

final class DeterministicActiveProjectionGenerationBootstrapV1Test extends TestCase
{
    private const string GENERATION = 'da5484c1-a351-4683-9881-79f34c51197f';

    private const string LISTING = '979cd5aa-ced1-48a1-8adf-8b29c843a0c2';

    public function test_initial_bootstrap_activates_non_empty_candidate_and_replay_is_idempotent(): void
    {
        $runtime = new BootstrapMemoryRuntime(self::LISTING);
        $service = $runtime->service();
        $command = new BootstrapActiveProjectionGenerationCommand(self::GENERATION, [self::LISTING], 'operator:test');
        self::assertNotNull($runtime->inspect(self::LISTING, PublicProjectionGenerationId::fromString(self::GENERATION))->record);

        $first = $service->bootstrap($command);
        self::assertSame(BootstrapActiveProjectionGenerationStatus::Applied, $first->status);
        self::assertSame(1, $first->processed);
        self::assertSame(1, $first->applied);
        self::assertSame(1, $first->manifestCount);
        self::assertTrue($first->validationPassed);
        self::assertSame('found', $first->activeReaderStatus);
        self::assertNotNull($first->state);
        self::assertSame(1, $first->state->activeCount);

        $replay = $service->bootstrap($command);
        self::assertSame(BootstrapActiveProjectionGenerationStatus::AlreadyApplied, $replay->status);
        self::assertNotNull($replay->state);
        self::assertSame(1, $replay->state->generationsCount);
        self::assertSame(1, $replay->state->projectionsCount);
    }

    public function test_different_active_generation_is_rejected_before_write(): void
    {
        $runtime = new BootstrapMemoryRuntime(self::LISTING);
        $runtime->activeId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $result = $runtime->service()->bootstrap(new BootstrapActiveProjectionGenerationCommand(self::GENERATION, [self::LISTING], 'operator:test'));

        self::assertSame(BootstrapActiveProjectionGenerationStatus::ActiveGenerationExists, $result->status);
        self::assertSame(0, $runtime->writes);
    }

    public function test_command_rejects_non_v4_generation_empty_scope_and_duplicate_scope(): void
    {
        foreach ([
            ['da5484c1-a351-3683-9881-79f34c51197f', [self::LISTING]],
            [self::GENERATION, []],
            [self::GENERATION, [self::LISTING, self::LISTING]],
        ] as [$generation, $scope]) {
            try {
                new BootstrapActiveProjectionGenerationCommand($generation, $scope, 'operator:test');
                self::fail('Invalid bootstrap command was accepted.');
            } catch (InvalidArgumentException) {
                self::addToAssertionCount(1);
            }
        }
    }
}

final class BootstrapMemoryRuntime implements InspectablePublicProjectionCandidateFactory, PublicListingProjectionWriter, PublicProjectionGenerationManager, PublicProjectionGenerationValidator, PublicProjectionRebuildEnumerator
{
    public ?string $activeId = null;

    public ?string $candidateId = null;

    public int $writes = 0;

    /** @var array<string, PublicListingProjectionRecord> */
    private array $records = [];

    public function __construct(private readonly string $listingId) {}

    public function service(): DeterministicActiveProjectionGenerationBootstrapV1
    {
        return new DeterministicActiveProjectionGenerationBootstrapV1(new BootstrapStateView($this), $this, $this, new PublicProjectionRebuilder($this, $this, $this, 10), $this, new BootstrapActiveReaderView($this));
    }

    public function inspect(string $listingId, PublicProjectionGenerationId $generationId): CandidateBuildResult
    {
        return CandidateBuildResult::built($listingId, $this->record($listingId, $generationId));
    }

    public function rebuild(string $listingId, PublicProjectionGenerationId $generationId): PublicListingProjectionRecord
    {
        return $this->record($listingId, $generationId);
    }

    public function createCandidate(PublicProjectionGenerationId $generationId): PublicProjectionGenerationTransition
    {
        if ($this->candidateId === $generationId->value) {
            return PublicProjectionGenerationTransition::AlreadyApplied;
        }
        $this->candidateId = $generationId->value;

        return PublicProjectionGenerationTransition::Applied;
    }

    public function activate(PublicProjectionGenerationId $generationId, PublicProjectionGenerationManifest $manifest): PublicProjectionGenerationTransition
    {
        if ($this->activeId === $generationId->value) {
            return PublicProjectionGenerationTransition::AlreadyApplied;
        }
        $this->activeId = $generationId->value;
        $this->candidateId = null;

        return PublicProjectionGenerationTransition::Applied;
    }

    public function rollback(PublicProjectionGenerationId $generationId): PublicProjectionGenerationTransition
    {
        return PublicProjectionGenerationTransition::InvalidState;
    }

    public function validate(PublicProjectionGenerationId $generationId, PublicProjectionGenerationManifest $manifest): PublicProjectionGenerationValidation
    {
        return new PublicProjectionGenerationValidation($generationId, $manifest->count(), count($this->records), [], [], []);
    }

    public function page(PublicProjectionRebuildScope $scope, ?string $checkpoint, int $limit): PublicProjectionRebuildPage
    {
        return new PublicProjectionRebuildPage([$this->listingId], null);
    }

    public function writeCandidate(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        if (isset($this->records[$record->listingId])) {
            return PublicProjectionWriteResult::AlreadyApplied;
        }
        $this->records[$record->listingId] = $record;
        $this->writes++;

        return PublicProjectionWriteResult::Applied;
    }

    public function applyCurrent(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        return PublicProjectionWriteResult::GenerationMismatch;
    }

    public function replaceCanonical(string $previousCanonicalPath, PublicListingProjectionRecord $replacement): PublicProjectionWriteResult
    {
        return PublicProjectionWriteResult::GenerationMismatch;
    }

    public function applyTombstone(PublicListingProjectionRecord $tombstone): PublicProjectionWriteResult
    {
        return PublicProjectionWriteResult::GenerationMismatch;
    }

    private function record(string $listingId, PublicProjectionGenerationId $generationId): PublicListingProjectionRecord
    {
        return PublicListingProjectionRecord::current($listingId, 'annonces/'.$listingId, PublicListingReadModelFixture::make(listingId: $listingId, canonicalUrl: 'https://appart.sn/annonces/'.$listingId), new PublicProjectionWatermark(3, 1, 1, 1, 1, 3, 1), $generationId);
    }

    public function state(): ActiveGenerationBootstrapState
    {
        return new ActiveGenerationBootstrapState(($this->activeId ?? $this->candidateId) === null ? 0 : 1, $this->activeId === null ? 0 : 1, count($this->records), $this->activeId, $this->candidateId === null ? [] : [$this->candidateId]);
    }
}

final readonly class BootstrapStateView implements ActiveGenerationBootstrapStateReader
{
    public function __construct(private BootstrapMemoryRuntime $runtime) {}

    public function read(): ActiveGenerationBootstrapState
    {
        return $this->runtime->state();
    }
}

final readonly class BootstrapActiveReaderView implements ActiveGenerationReader
{
    public function __construct(private BootstrapMemoryRuntime $runtime) {}

    public function read(): ActiveGenerationReadResult
    {
        if ($this->runtime->activeId === null) {
            return ActiveGenerationReadResult::missing();
        }

        return ActiveGenerationReadResult::found(new PublicProjectionGeneration(PublicProjectionGenerationId::fromString($this->runtime->activeId), PublicProjectionGenerationState::Active));
    }
}
