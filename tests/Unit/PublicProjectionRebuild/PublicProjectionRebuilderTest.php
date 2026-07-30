<?php

namespace Tests\Unit\PublicProjectionRebuild;

use App\Application\PublicProjectionRebuild\Contract\PublicProjectionCandidateFactory;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionRebuildEnumerator;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuilder;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuildPage;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuildScope;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuildScopeType;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;
use PHPUnit\Framework\TestCase;
use Tests\Support\PublicListingReadModelFixture;

final class PublicProjectionRebuilderTest extends TestCase
{
    private const string A = '97000000-0000-4000-8000-000000000011';

    private const string B = '97000000-0000-4000-8000-000000000012';

    private const string C = '97000000-0000-4000-8000-000000000013';

    public function test_full_rebuild_is_bounded_resumable_and_idempotent(): void
    {
        $generation = $this->generation();
        $enumerator = new InMemoryRebuildEnumerator([self::A, self::B, self::C]);
        $factory = new InMemoryCandidateFactory($generation);
        $writer = new InMemoryCandidateWriter;
        $rebuilder = new PublicProjectionRebuilder($enumerator, $factory, $writer, 2);

        $first = $rebuilder->runOnce($generation, PublicProjectionRebuildScope::full());
        $second = $rebuilder->runOnce($generation, PublicProjectionRebuildScope::full(), $first->nextCheckpoint);
        $repeat = $rebuilder->runOnce($generation, PublicProjectionRebuildScope::full());

        self::assertSame([2, 2, 0, false, '2'], [$first->processed, $first->applied, $first->alreadyApplied, $first->isComplete(), $first->nextCheckpoint]);
        self::assertSame([1, 1, true], [$second->processed, $second->applied, $second->isComplete()]);
        self::assertSame(2, $repeat->alreadyApplied);
        self::assertCount(3, $writer->records);
    }

    public function test_partial_and_range_scopes_are_explicitly_forwarded(): void
    {
        $generation = $this->generation();
        $enumerator = new InMemoryRebuildEnumerator([self::A, self::B, self::C]);
        $rebuilder = new PublicProjectionRebuilder($enumerator, new InMemoryCandidateFactory($generation), new InMemoryCandidateWriter, 10);

        $partial = $rebuilder->runOnce($generation, PublicProjectionRebuildScope::listings([self::B]));
        self::assertSame(PublicProjectionRebuildScopeType::Listings, $enumerator->lastScope?->type);
        self::assertSame(1, $partial->processed);

        $range = $rebuilder->runOnce($generation, PublicProjectionRebuildScope::range(self::B, self::C));
        self::assertSame(PublicProjectionRebuildScopeType::Range, $enumerator->lastScope?->type);
        self::assertSame(2, $range->processed);
    }

    public function test_missing_and_inconsistent_candidates_are_observable_without_silent_repair(): void
    {
        $generation = $this->generation();
        $factory = new InMemoryCandidateFactory($generation, [self::A]);
        $factory->wrongListing = self::B;
        $report = (new PublicProjectionRebuilder(new InMemoryRebuildEnumerator([self::A, self::B]), $factory, new InMemoryCandidateWriter, 10))
            ->runOnce($generation, PublicProjectionRebuildScope::full());

        self::assertSame(1, $report->missing);
        self::assertSame([self::B], $report->rejectedListingIds);
    }

    private function generation(): PublicProjectionGenerationId
    {
        return PublicProjectionGenerationId::fromString('97000000-0000-4000-8000-000000000001');
    }
}

final class InMemoryRebuildEnumerator implements PublicProjectionRebuildEnumerator
{
    public ?PublicProjectionRebuildScope $lastScope = null;

    /** @param list<string> $ids */
    public function __construct(private readonly array $ids) {}

    public function page(PublicProjectionRebuildScope $scope, ?string $checkpoint, int $limit): PublicProjectionRebuildPage
    {
        $this->lastScope = $scope;
        $ids = match ($scope->type) {
            PublicProjectionRebuildScopeType::Full => $this->ids,
            PublicProjectionRebuildScopeType::Listings => array_values(array_intersect($this->ids, $scope->listingIds)),
            PublicProjectionRebuildScopeType::Range => array_values(array_filter($this->ids, fn (string $id): bool => strcmp($id, (string) $scope->from) >= 0 && strcmp($id, (string) $scope->to) <= 0)),
        };
        $offset = $checkpoint === null ? 0 : (int) $checkpoint;
        $page = array_slice($ids, $offset, $limit);
        $next = $offset + count($page) < count($ids) ? (string) ($offset + count($page)) : null;

        return new PublicProjectionRebuildPage($page, $next);
    }
}

final class InMemoryCandidateFactory implements PublicProjectionCandidateFactory
{
    public ?string $wrongListing = null;

    /** @param list<string> $missing */
    public function __construct(private readonly PublicProjectionGenerationId $generation, private readonly array $missing = []) {}

    public function rebuild(string $listingId, PublicProjectionGenerationId $generationId): ?PublicListingProjectionRecord
    {
        if (in_array($listingId, $this->missing, true)) {
            return null;
        }
        $actual = $listingId === $this->wrongListing ? '97000000-0000-4000-8000-000000000099' : $listingId;
        $model = PublicListingReadModelFixture::make(listingId: $actual, canonicalUrl: 'https://appart.sn/annonces/'.$actual);

        return PublicListingProjectionRecord::current($actual, 'annonces/'.$actual, $model, new PublicProjectionWatermark(1, 1, 1, 1, 1, 1, 1), $this->generation);
    }
}

final class InMemoryCandidateWriter implements PublicListingProjectionWriter
{
    /** @var array<string, PublicListingProjectionRecord> */
    public array $records = [];

    public function writeCandidate(PublicListingProjectionRecord $record): PublicProjectionWriteResult
    {
        if (isset($this->records[$record->listingId])) {
            return PublicProjectionWriteResult::AlreadyApplied;
        }
        $this->records[$record->listingId] = $record;

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
}
