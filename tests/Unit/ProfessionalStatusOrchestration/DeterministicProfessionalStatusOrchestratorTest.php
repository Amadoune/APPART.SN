<?php

namespace Tests\Unit\ProfessionalStatusOrchestration;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusWorkflow;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\DeterministicProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusTransitionRequest;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusPersistenceReadResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusStoredState;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualTransitionStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextChecksum;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualAppend;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualAppendInspection;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualInspectionResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualWriteResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusExpectedVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusReplayPolicy;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusTransitionContext;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DeterministicProfessionalStatusOrchestratorTest extends TestCase
{
    #[DataProvider('nominalCases')]
    public function test_nominal_results_are_exhaustively_mapped(ProfessionalStatusPersistenceReadResult $read, ProfessionalStatusAction $action, ProfessionalStatusContextualWriteResult $write, ProfessionalStatusOrchestrationStatus $expected, int $expectedAppends): void
    {
        $store = new ControlledProfessionalStatusStore($read, $write);
        $orchestrator = new DeterministicProfessionalStatusOrchestrator($store, new ControlledProfessionalStatusInspector(ProfessionalStatusContextualInspectionResult::missing($this->id())), new ProfessionalStatusReplayPolicy, new ProfessionalStatusWorkflow);

        self::assertSame($expected, $orchestrator->execute($this->request($action))->status);
        self::assertSame($expectedAppends, $store->appends);
    }

    #[DataProvider('replayCases')]
    public function test_replay_never_appends_and_uses_exact_inspection(ProfessionalStatusContextualInspectionResult $inspection, ProfessionalStatusTransitionContext $context, ProfessionalStatusAction $action, ProfessionalStatusOrchestrationStatus $expected): void
    {
        $store = new ControlledProfessionalStatusStore($this->found(ProfessionalStatusState::Suspended, 2), ProfessionalStatusContextualWriteResult::Applied);
        $orchestrator = new DeterministicProfessionalStatusOrchestrator($store, new ControlledProfessionalStatusInspector($inspection), new ProfessionalStatusReplayPolicy, new ProfessionalStatusWorkflow);

        self::assertSame($expected, $orchestrator->execute(new ProfessionalStatusTransitionRequest($this->id(), $action, $context))->status);
        self::assertSame(0, $store->appends);
    }

    public function test_result_set_is_closed(): void
    {
        self::assertSame(['applied', 'already_applied', 'missing', 'version_conflict', 'denied', 'state_conflict', 'context_divergence', 'persistence_corrupted'], array_column(ProfessionalStatusOrchestrationStatus::cases(), 'value'));
    }

    public static function nominalCases(): array
    {
        $id = self::staticId();

        return [
            'missing' => [ProfessionalStatusPersistenceReadResult::missing($id), ProfessionalStatusAction::Suspend, ProfessionalStatusContextualWriteResult::Applied, ProfessionalStatusOrchestrationStatus::Missing, 0],
            'corrupted' => [ProfessionalStatusPersistenceReadResult::corrupted($id), ProfessionalStatusAction::Suspend, ProfessionalStatusContextualWriteResult::Applied, ProfessionalStatusOrchestrationStatus::PersistenceCorrupted, 0],
            'version' => [self::staticFound(ProfessionalStatusState::Active, 3), ProfessionalStatusAction::Suspend, ProfessionalStatusContextualWriteResult::Applied, ProfessionalStatusOrchestrationStatus::VersionConflict, 0],
            'denied' => [self::staticFound(ProfessionalStatusState::Active, 1), ProfessionalStatusAction::Reactivate, ProfessionalStatusContextualWriteResult::Applied, ProfessionalStatusOrchestrationStatus::Denied, 0],
            'applied' => [self::staticFound(ProfessionalStatusState::Active, 1), ProfessionalStatusAction::Suspend, ProfessionalStatusContextualWriteResult::Applied, ProfessionalStatusOrchestrationStatus::Applied, 1],
            'state conflict' => [self::staticFound(ProfessionalStatusState::Active, 1), ProfessionalStatusAction::Suspend, ProfessionalStatusContextualWriteResult::StateConflict, ProfessionalStatusOrchestrationStatus::StateConflict, 1],
        ];
    }

    public static function replayCases(): array
    {
        $snapshot = self::staticInspection();
        $same = self::staticContext('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');

        return [
            'already' => [ProfessionalStatusContextualInspectionResult::found($snapshot), $same, ProfessionalStatusAction::Suspend, ProfessionalStatusOrchestrationStatus::AlreadyApplied],
            'context divergence' => [ProfessionalStatusContextualInspectionResult::found($snapshot), self::staticContext('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'), ProfessionalStatusAction::Suspend, ProfessionalStatusOrchestrationStatus::ContextDivergence],
            'action conflict' => [ProfessionalStatusContextualInspectionResult::found($snapshot), $same, ProfessionalStatusAction::Reactivate, ProfessionalStatusOrchestrationStatus::StateConflict],
            'missing inspection' => [ProfessionalStatusContextualInspectionResult::missing(self::staticId()), $same, ProfessionalStatusAction::Suspend, ProfessionalStatusOrchestrationStatus::VersionConflict],
            'corrupted inspection' => [ProfessionalStatusContextualInspectionResult::corrupted(self::staticId()), $same, ProfessionalStatusAction::Suspend, ProfessionalStatusOrchestrationStatus::PersistenceCorrupted],
        ];
    }

    private function request(ProfessionalStatusAction $action): ProfessionalStatusTransitionRequest
    {
        return new ProfessionalStatusTransitionRequest($this->id(), $action, self::staticContext('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    }

    private function found(ProfessionalStatusState $state, int $version): ProfessionalStatusPersistenceReadResult
    {
        return self::staticFound($state, $version);
    }

    private function id(): ProfessionalStatusId
    {
        return self::staticId();
    }

    private static function staticFound(ProfessionalStatusState $state, int $version): ProfessionalStatusPersistenceReadResult
    {
        return ProfessionalStatusPersistenceReadResult::found(new ProfessionalStatusStoredState(self::staticId(), $state, $version));
    }

    private static function staticInspection(): ProfessionalStatusContextualAppendInspection
    {
        return new ProfessionalStatusContextualAppendInspection(self::staticId(), 2, new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Suspended, ProfessionalStatusAction::Suspend), self::staticContext('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')->actor, self::staticContext('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')->occurredAt, ProfessionalStatusContextChecksum::fromString(str_repeat('a', 64)));
    }

    private static function staticContext(string $actor): ProfessionalStatusTransitionContext
    {
        return new ProfessionalStatusTransitionContext(ProfessionalStatusActorId::fromString($actor), ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00')), new ProfessionalStatusExpectedVersion(1));
    }

    private static function staticId(): ProfessionalStatusId
    {
        return ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000045');
    }
}

final class ControlledProfessionalStatusStore implements ProfessionalStatusContextualTransitionStore
{
    public int $appends = 0;

    public function __construct(private readonly ProfessionalStatusPersistenceReadResult $readResult, private readonly ProfessionalStatusContextualWriteResult $writeResult) {}

    public function read(ProfessionalStatusId $professionalId): ProfessionalStatusPersistenceReadResult
    {
        return $this->readResult;
    }

    public function append(ProfessionalStatusContextualAppend $append): ProfessionalStatusContextualWriteResult
    {
        $this->appends++;

        return $this->writeResult;
    }
}

final readonly class ControlledProfessionalStatusInspector implements ProfessionalStatusContextualReplayInspector
{
    public function __construct(private ProfessionalStatusContextualInspectionResult $result) {}

    public function inspectLatest(ProfessionalStatusId $professionalId): ProfessionalStatusContextualInspectionResult
    {
        return $this->result;
    }
}
