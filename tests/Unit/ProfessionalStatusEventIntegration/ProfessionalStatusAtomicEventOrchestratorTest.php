<?php

namespace Tests\Unit\ProfessionalStatusEventIntegration;

use App\Application\ProfessionalStatusEventIntegration\Contract\ProfessionalStatusAtomicTransaction;
use App\Application\ProfessionalStatusEventIntegration\ProfessionalStatusAtomicEventOrchestrator;
use App\Application\ProfessionalStatusEventIntegration\ProfessionalStatusAtomicEventRequest;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventCatalog;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\Contract\ProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusExpectedVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusTransitionContext;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProfessionalStatusAtomicEventOrchestratorTest extends TestCase
{
    /** @return iterable<string,array{ProfessionalStatusOrchestrationStatus}> */
    public static function terminalResults(): iterable
    {
        foreach ([ProfessionalStatusOrchestrationStatus::Missing, ProfessionalStatusOrchestrationStatus::VersionConflict, ProfessionalStatusOrchestrationStatus::Denied, ProfessionalStatusOrchestrationStatus::StateConflict, ProfessionalStatusOrchestrationStatus::ContextDivergence] as $status) {
            yield $status->value => [$status];
        }
    }

    #[DataProvider('terminalResults')]
    public function test_non_persisted_results_never_inspect_or_write(ProfessionalStatusOrchestrationStatus $status): void
    {
        $orchestrator = $this->createMock(ProfessionalStatusOrchestrator::class);
        $orchestrator->expects(self::once())->method('execute')->willReturn(new ProfessionalStatusOrchestrationResult($status));
        $inspector = $this->createMock(ProfessionalStatusContextualReplayInspector::class);
        $inspector->expects(self::never())->method('inspectLatest');
        $outbox = $this->createMock(PublicProjectionOutboxWriter::class);
        $outbox->expects(self::never())->method('append');
        $result = (new ProfessionalStatusAtomicEventOrchestrator($orchestrator, $inspector, new ImmediateProfessionalStatusAtomicTransaction, new ProfessionalStatusEventCatalog, new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog), $outbox, PublicProjectionOutboxConsumerId::fromString('unit-professional-integration')))->transition($this->request());
        self::assertSame($status, $result->status);
    }

    public function test_persistence_corruption_is_closed(): void
    {
        $orchestrator = $this->createMock(ProfessionalStatusOrchestrator::class);
        $orchestrator->method('execute')->willReturn(new ProfessionalStatusOrchestrationResult(ProfessionalStatusOrchestrationStatus::PersistenceCorrupted));
        $result = (new ProfessionalStatusAtomicEventOrchestrator($orchestrator, $this->createMock(ProfessionalStatusContextualReplayInspector::class), new ImmediateProfessionalStatusAtomicTransaction, new ProfessionalStatusEventCatalog, new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog), $this->createMock(PublicProjectionOutboxWriter::class), PublicProjectionOutboxConsumerId::fromString('unit-professional-integration')))->transition($this->request());
        self::assertSame(ProfessionalStatusOrchestrationStatus::PersistenceCorrupted, $result->status);
    }

    private function request(): ProfessionalStatusAtomicEventRequest
    {
        $occurred = ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T10:00:00Z'));

        return new ProfessionalStatusAtomicEventRequest(ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000499'), ProfessionalStatusAction::Suspend, new ProfessionalStatusTransitionContext(ProfessionalStatusActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $occurred, new ProfessionalStatusExpectedVersion(1)), ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T10:00:01Z')));
    }
}

final class ImmediateProfessionalStatusAtomicTransaction implements ProfessionalStatusAtomicTransaction
{
    public function run(Closure $operation): mixed
    {
        return $operation();
    }
}
