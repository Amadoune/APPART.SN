<?php

namespace Tests\Unit\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionAuthority;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionReasonEvidence;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleWorkflow;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleTransitionRequest;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\DeterministicAdministrativeActionLifecycleOrchestrator;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\AdministrativeActionLifecyclePersistenceReadResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\AdministrativeActionLifecycleStoredState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualAppendInspection;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualInspectionResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualWriteResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionExpectedVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionReplayPolicy;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionTransitionExecutionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualReplayInspector;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualTransitionStore;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DeterministicAdministrativeActionLifecycleOrchestratorTest extends TestCase
{
    #[DataProvider('writeMappings')]
    public function test_nominal_path_maps_every_store_result(
        AdministrativeActionContextualWriteResult $write,
        AdministrativeActionLifecycleOrchestrationStatus $expected,
    ): void {
        $store = $this->createMock(AdministrativeActionContextualTransitionStore::class);
        $store->method('read')->willReturn($this->found(1, AdministrativeActionLifecycleState::Draft));
        $store->expects(self::once())->method('append')->willReturn($write);

        self::assertSame($expected, $this->orchestrator($store)->execute($this->request())->status);
    }

    public function test_denied_decision_never_appends(): void
    {
        $store = $this->createMock(AdministrativeActionContextualTransitionStore::class);
        $store->method('read')->willReturn($this->found(1, AdministrativeActionLifecycleState::Draft));
        $store->expects(self::never())->method('append');
        $request = new AdministrativeActionLifecycleTransitionRequest(
            $this->id(),
            AdministrativeActionLifecycleAction::Approve,
            $this->context(),
        );

        $result = $this->orchestrator($store)->execute($request);
        self::assertSame(AdministrativeActionLifecycleOrchestrationStatus::Denied, $result->status);
        self::assertNotNull($result->diagnostic);
    }

    public function test_missing_corrupted_and_wrong_version_stop_before_workflow_persistence(): void
    {
        foreach ([
            [AdministrativeActionLifecyclePersistenceReadResult::notEnrolled($this->id()), AdministrativeActionLifecycleOrchestrationStatus::Missing],
            [AdministrativeActionLifecyclePersistenceReadResult::corrupted($this->id()), AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted],
            [$this->found(8, AdministrativeActionLifecycleState::Draft), AdministrativeActionLifecycleOrchestrationStatus::VersionConflict],
        ] as [$read, $expected]) {
            $store = $this->createMock(AdministrativeActionContextualTransitionStore::class);
            $store->method('read')->willReturn($read);
            $store->expects(self::never())->method('append');
            self::assertSame($expected, $this->orchestrator($store)->execute($this->request())->status);
        }
    }

    public function test_replay_uses_exact_inspection_without_append(): void
    {
        $context = $this->context();
        $store = $this->createMock(AdministrativeActionContextualTransitionStore::class);
        $store->method('read')->willReturn($this->found(2, AdministrativeActionLifecycleState::Recorded));
        $store->expects(self::never())->method('append');
        $inspector = $this->createMock(AdministrativeActionContextualReplayInspector::class);
        $inspector->expects(self::once())->method('inspectLatest')->willReturn(
            AdministrativeActionContextualInspectionResult::found(new AdministrativeActionContextualAppendInspection(
                $this->id(),
                2,
                new AdministrativeActionLifecycleTransition(
                    AdministrativeActionLifecycleState::Draft,
                    AdministrativeActionLifecycleState::Recorded,
                    AdministrativeActionLifecycleAction::Record,
                ),
                $context,
                $context->checksum(),
            )),
        );

        self::assertSame(
            AdministrativeActionLifecycleOrchestrationStatus::AlreadyApplied,
            $this->orchestrator($store, $inspector)->execute($this->request())->status,
        );
    }

    /** @return iterable<string, array{AdministrativeActionContextualWriteResult, AdministrativeActionLifecycleOrchestrationStatus}> */
    public static function writeMappings(): iterable
    {
        yield 'applied' => [AdministrativeActionContextualWriteResult::Applied, AdministrativeActionLifecycleOrchestrationStatus::Applied];
        yield 'already' => [AdministrativeActionContextualWriteResult::AlreadyApplied, AdministrativeActionLifecycleOrchestrationStatus::AlreadyApplied];
        yield 'context divergence' => [AdministrativeActionContextualWriteResult::ContextDivergence, AdministrativeActionLifecycleOrchestrationStatus::ContextDivergence];
        yield 'version' => [AdministrativeActionContextualWriteResult::VersionConflict, AdministrativeActionLifecycleOrchestrationStatus::VersionConflict];
        yield 'state' => [AdministrativeActionContextualWriteResult::StateConflict, AdministrativeActionLifecycleOrchestrationStatus::StateConflict];
        yield 'transition' => [AdministrativeActionContextualWriteResult::TransitionDivergence, AdministrativeActionLifecycleOrchestrationStatus::TransitionDivergence];
        yield 'enrollment' => [AdministrativeActionContextualWriteResult::EnrollmentDivergence, AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted];
        yield 'corrupted' => [AdministrativeActionContextualWriteResult::Corrupted, AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted];
        yield 'persistence' => [AdministrativeActionContextualWriteResult::PersistenceCorrupted, AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted];
    }

    private function orchestrator(
        AdministrativeActionContextualTransitionStore $store,
        ?AdministrativeActionContextualReplayInspector $inspector = null,
    ): DeterministicAdministrativeActionLifecycleOrchestrator {
        return new DeterministicAdministrativeActionLifecycleOrchestrator(
            $store,
            $inspector ?? $this->createMock(AdministrativeActionContextualReplayInspector::class),
            new AdministrativeActionReplayPolicy,
            new AdministrativeActionLifecycleWorkflow,
        );
    }

    private function request(): AdministrativeActionLifecycleTransitionRequest
    {
        return new AdministrativeActionLifecycleTransitionRequest($this->id(), AdministrativeActionLifecycleAction::Record, $this->context());
    }

    private function context(): AdministrativeActionTransitionExecutionContext
    {
        $actor = ActorId::fromString('author-001');
        $decision = new AdministrativeActionDecisionContext(
            AdministrativeActionDecisionContextVersion::V1,
            AdministrativeActionReasonEvidence::Present,
            AdministrativeActionDecisionAuthority::directRecording($actor, $actor),
        );

        return AdministrativeActionTransitionExecutionContext::record(
            new AdministrativeActionExpectedVersion(1),
            $actor,
            AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T12:00:00.000000+00:00')),
            AuditReason::fromString('Explicit orchestration historical reason.'),
            $decision,
        );
    }

    private function found(int $version, AdministrativeActionLifecycleState $state): AdministrativeActionLifecyclePersistenceReadResult
    {
        return AdministrativeActionLifecyclePersistenceReadResult::found(
            new AdministrativeActionLifecycleStoredState($this->id(), $version, $state, str_repeat('a', 64)),
        );
    }

    private function id(): AdministrativeActionId
    {
        return AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000047');
    }
}
