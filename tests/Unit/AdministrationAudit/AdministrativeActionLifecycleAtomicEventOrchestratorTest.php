<?php

namespace Tests\Unit\AdministrationAudit;

use App\Application\AdministrativeActionLifecycleEventIntegration\AdministrativeActionLifecycleAtomicEventOrchestrator;
use App\Application\AdministrativeActionLifecycleEventIntegration\AdministrativeActionLifecycleAtomicEventRequest;
use App\Application\AdministrativeActionLifecycleEventIntegration\Contract\AdministrativeActionLifecycleAtomicTransaction;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionAuthority;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionDecisionContextVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext\AdministrativeActionReasonEvidence;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventCatalog;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\Contract\AdministrativeActionLifecycleOrchestrator;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionExpectedVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionTransitionExecutionContext;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualReplayInspector;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdministrativeActionLifecycleAtomicEventOrchestratorTest extends TestCase
{
    /** @return iterable<string, array{AdministrativeActionLifecycleOrchestrationStatus}> */
    public static function nonAppliedResults(): iterable
    {
        foreach ([
            AdministrativeActionLifecycleOrchestrationStatus::Missing,
            AdministrativeActionLifecycleOrchestrationStatus::VersionConflict,
            AdministrativeActionLifecycleOrchestrationStatus::Denied,
            AdministrativeActionLifecycleOrchestrationStatus::StateConflict,
            AdministrativeActionLifecycleOrchestrationStatus::ContextDivergence,
            AdministrativeActionLifecycleOrchestrationStatus::TransitionDivergence,
        ] as $status) {
            yield $status->value => [$status];
        }
    }

    #[DataProvider('nonAppliedResults')]
    public function test_non_applied_results_never_inspect_or_write(
        AdministrativeActionLifecycleOrchestrationStatus $status,
    ): void {
        $orchestrator = $this->createMock(AdministrativeActionLifecycleOrchestrator::class);
        $orchestrator->expects(self::once())->method('execute')->willReturn(
            new AdministrativeActionLifecycleOrchestrationResult($status),
        );
        $inspector = $this->createMock(AdministrativeActionContextualReplayInspector::class);
        $inspector->expects(self::never())->method('inspectLatest');
        $outbox = $this->createMock(PublicProjectionOutboxWriter::class);
        $outbox->expects(self::never())->method('append');

        $result = $this->integrator($orchestrator, $inspector, $outbox)->transition($this->request());

        self::assertSame($status, $result->status);
    }

    public function test_persistence_corruption_is_closed_without_outbox_write(): void
    {
        $orchestrator = $this->createMock(AdministrativeActionLifecycleOrchestrator::class);
        $orchestrator->method('execute')->willReturn(new AdministrativeActionLifecycleOrchestrationResult(
            AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted,
        ));
        $outbox = $this->createMock(PublicProjectionOutboxWriter::class);
        $outbox->expects(self::never())->method('append');

        $result = $this->integrator(
            $orchestrator,
            $this->createMock(AdministrativeActionContextualReplayInspector::class),
            $outbox,
        )->transition($this->request());

        self::assertSame(AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted, $result->status);
    }

    private function integrator(
        AdministrativeActionLifecycleOrchestrator $orchestrator,
        AdministrativeActionContextualReplayInspector $inspector,
        PublicProjectionOutboxWriter $outbox,
    ): AdministrativeActionLifecycleAtomicEventOrchestrator {
        return new AdministrativeActionLifecycleAtomicEventOrchestrator(
            $orchestrator,
            $inspector,
            new ImmediateAdministrativeActionLifecycleAtomicTransaction,
            new AdministrativeActionLifecycleEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            $outbox,
            PublicProjectionOutboxConsumerId::fromString('unit-administrative-action-integration'),
        );
    }

    private function request(): AdministrativeActionLifecycleAtomicEventRequest
    {
        $actor = ActorId::fromString('author-001');
        $occurredAt = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
            new DateTimeImmutable('2026-07-25T10:00:00Z'),
        );
        $decision = new AdministrativeActionDecisionContext(
            AdministrativeActionDecisionContextVersion::V1,
            AdministrativeActionReasonEvidence::Present,
            AdministrativeActionDecisionAuthority::directRecording($actor, $actor),
        );
        $context = AdministrativeActionTransitionExecutionContext::record(
            new AdministrativeActionExpectedVersion(1),
            $actor,
            $occurredAt,
            AuditReason::fromString('Explicit atomic integration reason.'),
            $decision,
        );

        return new AdministrativeActionLifecycleAtomicEventRequest(
            AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000470'),
            AdministrativeActionLifecycleAction::Record,
            $context,
            AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(
                new DateTimeImmutable('2026-07-25T10:00:01Z'),
            ),
        );
    }
}

final class ImmediateAdministrativeActionLifecycleAtomicTransaction implements AdministrativeActionLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed
    {
        return $operation();
    }
}
