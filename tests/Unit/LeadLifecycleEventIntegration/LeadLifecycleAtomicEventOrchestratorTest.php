<?php

namespace Tests\Unit\LeadLifecycleEventIntegration;

use App\Application\LeadLifecycleEventIntegration\Contract\LeadLifecycleAtomicTransaction;
use App\Application\LeadLifecycleEventIntegration\LeadLifecycleAtomicEventOrchestrator;
use App\Application\LeadLifecycleEventIntegration\LeadLifecycleAtomicEventRequest;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventCatalog;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\Contract\LeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationStatus;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualReplayInspector;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LeadLifecycleAtomicEventOrchestratorTest extends TestCase
{
    /** @return iterable<string, array{LeadLifecycleOrchestrationStatus}> */
    public static function terminalResults(): iterable
    {
        yield 'missing' => [LeadLifecycleOrchestrationStatus::Missing];
        yield 'version conflict' => [LeadLifecycleOrchestrationStatus::VersionConflict];
        yield 'denied' => [LeadLifecycleOrchestrationStatus::Denied];
        yield 'state conflict' => [LeadLifecycleOrchestrationStatus::StateConflict];
        yield 'context divergence' => [LeadLifecycleOrchestrationStatus::ContextDivergence];
    }

    #[DataProvider('terminalResults')]
    public function test_non_persisted_results_never_inspect_or_write_outbox(LeadLifecycleOrchestrationStatus $status): void
    {
        $orchestrator = $this->createMock(LeadLifecycleOrchestrator::class);
        $orchestrator->expects(self::once())->method('execute')->willReturn(new LeadLifecycleOrchestrationResult($status));
        $inspector = $this->createMock(LeadLifecycleContextualReplayInspector::class);
        $inspector->expects(self::never())->method('inspectLatest');
        $outbox = $this->createMock(PublicProjectionOutboxWriter::class);
        $outbox->expects(self::never())->method('append');

        $result = (new LeadLifecycleAtomicEventOrchestrator(
            $orchestrator,
            $inspector,
            new ImmediateLeadAtomicTransaction,
            new LeadLifecycleEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            $outbox,
            PublicProjectionOutboxConsumerId::fromString('unit-lead-integration'),
        ))->transition($this->request());

        self::assertSame($status, $result->status);
    }

    public function test_persistence_corruption_is_returned_as_closed_result(): void
    {
        $orchestrator = $this->createMock(LeadLifecycleOrchestrator::class);
        $orchestrator->method('execute')->willReturn(new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::PersistenceCorrupted));

        $result = (new LeadLifecycleAtomicEventOrchestrator(
            $orchestrator,
            $this->createMock(LeadLifecycleContextualReplayInspector::class),
            new ImmediateLeadAtomicTransaction,
            new LeadLifecycleEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            $this->createMock(PublicProjectionOutboxWriter::class),
            PublicProjectionOutboxConsumerId::fromString('unit-lead-integration'),
        ))->transition($this->request());

        self::assertSame(LeadLifecycleOrchestrationStatus::PersistenceCorrupted, $result->status);
    }

    private function request(): LeadLifecycleAtomicEventRequest
    {
        return new LeadLifecycleAtomicEventRequest(
            LeadId::fromString('a4400000-0000-4000-8000-000000000499'),
            LeadLifecycleAction::Deliver,
            1,
            LeadLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
            LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T10:00:00Z')),
            LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T10:00:01Z')),
        );
    }
}

final class ImmediateLeadAtomicTransaction implements LeadLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed
    {
        return $operation();
    }
}
