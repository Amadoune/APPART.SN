<?php

namespace Tests\Unit\MediaItemLifecycleOrchestration;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleWorkflow;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualTransitionStore;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionDecisionVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionTransitionDecision;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualAppend;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualAppendInspection;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualInspectionResult;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualWriteResult;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleExpectedVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleReplayPolicy;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleTransitionContext;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\DeterministicMediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationStatus;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleTransitionRequest;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecyclePersistenceReadResult;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleStoredState;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DeterministicMediaItemLifecycleOrchestratorTest extends TestCase
{
    #[DataProvider('nominalCases')]
    public function test_nominal_results_are_exhaustively_mapped(MediaItemLifecyclePersistenceReadResult $read, MediaItemLifecycleAction $action, MediaItemLifecycleContextualWriteResult $write, MediaItemLifecycleOrchestrationStatus $expected, int $appends): void
    {
        $store = new ControlledMediaItemLifecycleStore($read, $write);
        $orchestrator = new DeterministicMediaItemLifecycleOrchestrator($store, new ControlledMediaItemLifecycleInspector(MediaItemLifecycleContextualInspectionResult::missing($this->id())), new MediaItemLifecycleReplayPolicy, new MediaItemLifecycleWorkflow);

        self::assertSame($expected, $orchestrator->execute($this->request($action))->status);
        self::assertSame($appends, $store->appends);
    }

    #[DataProvider('replayCases')]
    public function test_replay_never_appends_or_calls_the_workflow(MediaItemLifecycleContextualInspectionResult $inspection, MediaItemLifecycleTransitionContext $context, MediaItemLifecycleAction $action, MediaItemLifecycleOrchestrationStatus $expected): void
    {
        $store = new ControlledMediaItemLifecycleStore($this->found(MediaItemLifecycleState::Removed, 2), MediaItemLifecycleContextualWriteResult::Applied);
        $orchestrator = new DeterministicMediaItemLifecycleOrchestrator($store, new ControlledMediaItemLifecycleInspector($inspection), new MediaItemLifecycleReplayPolicy, new MediaItemLifecycleWorkflow);

        self::assertSame($expected, $orchestrator->execute(new MediaItemLifecycleTransitionRequest($this->id(), $action, $context))->status);
        self::assertSame(0, $store->appends);
    }

    public function test_result_set_is_closed(): void
    {
        self::assertSame(['applied', 'already_applied', 'missing', 'version_conflict', 'denied', 'state_conflict', 'context_divergence', 'persistence_corrupted'], array_column(MediaItemLifecycleOrchestrationStatus::cases(), 'value'));
    }

    /** @return array<string, array{MediaItemLifecyclePersistenceReadResult, MediaItemLifecycleAction, MediaItemLifecycleContextualWriteResult, MediaItemLifecycleOrchestrationStatus, int}> */
    public static function nominalCases(): array
    {
        $id = self::staticId();

        return [
            'missing' => [MediaItemLifecyclePersistenceReadResult::missing($id), MediaItemLifecycleAction::Remove, MediaItemLifecycleContextualWriteResult::Applied, MediaItemLifecycleOrchestrationStatus::Missing, 0],
            'corrupted' => [MediaItemLifecyclePersistenceReadResult::corrupted($id), MediaItemLifecycleAction::Remove, MediaItemLifecycleContextualWriteResult::Applied, MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted, 0],
            'version' => [self::staticFound(MediaItemLifecycleState::Active, 4), MediaItemLifecycleAction::Remove, MediaItemLifecycleContextualWriteResult::Applied, MediaItemLifecycleOrchestrationStatus::VersionConflict, 0],
            'denied' => [self::staticFound(MediaItemLifecycleState::Active, 1), MediaItemLifecycleAction::Unknown, MediaItemLifecycleContextualWriteResult::Applied, MediaItemLifecycleOrchestrationStatus::Denied, 0],
            'applied' => [self::staticFound(MediaItemLifecycleState::Active, 1), MediaItemLifecycleAction::Remove, MediaItemLifecycleContextualWriteResult::Applied, MediaItemLifecycleOrchestrationStatus::Applied, 1],
            'already from store race' => [self::staticFound(MediaItemLifecycleState::Active, 1), MediaItemLifecycleAction::Remove, MediaItemLifecycleContextualWriteResult::AlreadyApplied, MediaItemLifecycleOrchestrationStatus::AlreadyApplied, 1],
            'context race' => [self::staticFound(MediaItemLifecycleState::Active, 1), MediaItemLifecycleAction::Remove, MediaItemLifecycleContextualWriteResult::ContextDivergence, MediaItemLifecycleOrchestrationStatus::ContextDivergence, 1],
            'version race' => [self::staticFound(MediaItemLifecycleState::Active, 1), MediaItemLifecycleAction::Remove, MediaItemLifecycleContextualWriteResult::VersionConflict, MediaItemLifecycleOrchestrationStatus::VersionConflict, 1],
            'state conflict' => [self::staticFound(MediaItemLifecycleState::Active, 1), MediaItemLifecycleAction::Remove, MediaItemLifecycleContextualWriteResult::StateConflict, MediaItemLifecycleOrchestrationStatus::StateConflict, 1],
            'transition rejected' => [self::staticFound(MediaItemLifecycleState::Active, 1), MediaItemLifecycleAction::Remove, MediaItemLifecycleContextualWriteResult::TransitionRejected, MediaItemLifecycleOrchestrationStatus::StateConflict, 1],
            'write corrupted' => [self::staticFound(MediaItemLifecycleState::Active, 1), MediaItemLifecycleAction::Remove, MediaItemLifecycleContextualWriteResult::Corrupted, MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted, 1],
        ];
    }

    /** @return array<string, array{MediaItemLifecycleContextualInspectionResult, MediaItemLifecycleTransitionContext, MediaItemLifecycleAction, MediaItemLifecycleOrchestrationStatus}> */
    public static function replayCases(): array
    {
        $same = self::staticContext('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
        $snapshot = self::staticInspection($same);

        return [
            'already' => [MediaItemLifecycleContextualInspectionResult::found($snapshot), $same, MediaItemLifecycleAction::Remove, MediaItemLifecycleOrchestrationStatus::AlreadyApplied],
            'context divergence' => [MediaItemLifecycleContextualInspectionResult::found($snapshot), self::staticContext('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'), MediaItemLifecycleAction::Remove, MediaItemLifecycleOrchestrationStatus::ContextDivergence],
            'action conflict' => [MediaItemLifecycleContextualInspectionResult::found($snapshot), $same, MediaItemLifecycleAction::Archive, MediaItemLifecycleOrchestrationStatus::StateConflict],
            'missing inspection' => [MediaItemLifecycleContextualInspectionResult::missing(self::staticId()), $same, MediaItemLifecycleAction::Remove, MediaItemLifecycleOrchestrationStatus::VersionConflict],
            'corrupted inspection' => [MediaItemLifecycleContextualInspectionResult::corrupted(self::staticId()), $same, MediaItemLifecycleAction::Remove, MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted],
        ];
    }

    private function request(MediaItemLifecycleAction $action): MediaItemLifecycleTransitionRequest
    {
        return new MediaItemLifecycleTransitionRequest($this->id(), $action, self::staticContext('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    }

    private function found(MediaItemLifecycleState $state, int $version): MediaItemLifecyclePersistenceReadResult
    {
        return self::staticFound($state, $version);
    }

    private function id(): MediaItemLifecycleId
    {
        return self::staticId();
    }

    private static function staticFound(MediaItemLifecycleState $state, int $version): MediaItemLifecyclePersistenceReadResult
    {
        return MediaItemLifecyclePersistenceReadResult::found(new MediaItemLifecycleStoredState(self::staticId(), $state, $version));
    }

    private static function staticInspection(MediaItemLifecycleTransitionContext $context): MediaItemLifecycleContextualAppendInspection
    {
        return new MediaItemLifecycleContextualAppendInspection(
            self::staticId(),
            2,
            new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Removed, MediaItemLifecycleAction::Remove),
            $context,
            $context->checksum(),
        );
    }

    private static function staticContext(string $actor): MediaItemLifecycleTransitionContext
    {
        return new MediaItemLifecycleTransitionContext(
            MediaItemLifecycleContextVersion::V1,
            MediaCollectionId::fromString('a4601000-0000-4000-8000-000000000001'),
            MediaId::fromString(self::staticId()->value),
            new MediaItemLifecycleExpectedVersion(1),
            new MediaCollectionDecisionVersion(7),
            MediaItemLifecycleActorId::fromString($actor),
            MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T12:00:00.123456Z')),
            MediaCollectionTransitionDecision::notPrimary(),
        );
    }

    private static function staticId(): MediaItemLifecycleId
    {
        return MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000046');
    }
}

final class ControlledMediaItemLifecycleStore implements MediaItemLifecycleContextualTransitionStore
{
    public int $appends = 0;

    public function __construct(private readonly MediaItemLifecyclePersistenceReadResult $readResult, private readonly MediaItemLifecycleContextualWriteResult $writeResult) {}

    public function read(MediaItemLifecycleId $mediaId): MediaItemLifecyclePersistenceReadResult
    {
        return $this->readResult;
    }

    public function append(MediaItemLifecycleContextualAppend $append): MediaItemLifecycleContextualWriteResult
    {
        $this->appends++;

        return $this->writeResult;
    }
}

final readonly class ControlledMediaItemLifecycleInspector implements MediaItemLifecycleContextualReplayInspector
{
    public function __construct(private MediaItemLifecycleContextualInspectionResult $result) {}

    public function inspectLatest(MediaItemLifecycleId $mediaId): MediaItemLifecycleContextualInspectionResult
    {
        return $this->result;
    }
}
