<?php

namespace Tests\Unit\ProfessionalStatusEventRouting;

use App\Application\ProfessionalStatusEventRouting\DurableProfessionalStatusEventRouter;
use App\Application\ProfessionalStatusEventRouting\ProfessionalStatusInboxStore;
use App\Application\ProfessionalStatusEventRouting\ProfessionalStatusInboxStoreResult;
use App\Application\ProfessionalStatusEventRouting\ProfessionalStatusInboxStoreStatus;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusDeliveryPayload;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingDiagnostic;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingStatus;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportEnvelope;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEvent;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventId;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventMetadata;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayload;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayloadVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventType;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DurableProfessionalStatusEventRouterTest extends TestCase
{
    #[DataProvider('storeMappings')]
    public function test_store_results_are_mapped_exhaustively(ProfessionalStatusInboxStoreStatus $storeStatus, ProfessionalStatusEventRoutingStatus $routingStatus, ?ProfessionalStatusEventRoutingDiagnostic $diagnostic): void
    {
        $store = new class($storeStatus) implements ProfessionalStatusInboxStore
        {
            public function __construct(private ProfessionalStatusInboxStoreStatus $status) {}

            public function store(ProfessionalStatusTransportEnvelope $envelope): ProfessionalStatusInboxStoreResult
            {
                return new ProfessionalStatusInboxStoreResult($this->status);
            }
        };
        $result = (new DurableProfessionalStatusEventRouter($store))->route($this->envelope());
        self::assertSame($routingStatus, $result->status);
        self::assertSame($diagnostic, $result->diagnostic);
        self::assertSame($routingStatus === ProfessionalStatusEventRoutingStatus::Routed, $result->acknowledgesDelivery());
    }

    public function test_technical_exception_is_closed_retryable_failure(): void
    {
        $store = new class implements ProfessionalStatusInboxStore
        {
            public function store(ProfessionalStatusTransportEnvelope $envelope): ProfessionalStatusInboxStoreResult
            {
                throw new RuntimeException('technical');
            }
        };
        $result = (new DurableProfessionalStatusEventRouter($store))->route($this->envelope());
        self::assertSame(ProfessionalStatusEventRoutingStatus::RetryableFailure, $result->status);
        self::assertSame(ProfessionalStatusEventRoutingDiagnostic::TransferFailed, $result->diagnostic);
    }

    /** @return list<array{ProfessionalStatusInboxStoreStatus,ProfessionalStatusEventRoutingStatus,?ProfessionalStatusEventRoutingDiagnostic}> */
    public static function storeMappings(): array
    {
        return [
            [ProfessionalStatusInboxStoreStatus::Stored, ProfessionalStatusEventRoutingStatus::Routed, null],
            [ProfessionalStatusInboxStoreStatus::AlreadyStored, ProfessionalStatusEventRoutingStatus::Routed, null],
            [ProfessionalStatusInboxStoreStatus::Unavailable, ProfessionalStatusEventRoutingStatus::Deferred, ProfessionalStatusEventRoutingDiagnostic::RouteUnavailable],
            [ProfessionalStatusInboxStoreStatus::RetryableFailure, ProfessionalStatusEventRoutingStatus::RetryableFailure, ProfessionalStatusEventRoutingDiagnostic::TransferFailed],
            [ProfessionalStatusInboxStoreStatus::Rejected, ProfessionalStatusEventRoutingStatus::Rejected, ProfessionalStatusEventRoutingDiagnostic::CorruptedEvent],
        ];
    }

    private function envelope(): ProfessionalStatusTransportEnvelope
    {
        $transition = new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Suspended, ProfessionalStatusAction::Suspend);
        $id = ProfessionalStatusId::fromString('a4100000-0000-4000-8000-000000000099');
        $version = ProfessionalStatusEventPayloadVersion::V1;
        $eventId = ProfessionalStatusEventId::derive(ProfessionalStatusEventType::Suspended, $version, $id, $transition, 2);
        $at = ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));
        $event = new ProfessionalStatusEvent(new ProfessionalStatusEventMetadata(ProfessionalStatusEventType::Suspended, $version, ProfessionalStatusActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at), new ProfessionalStatusEventPayload($eventId, $id, 'active>suspend>suspended', $transition->from, $transition->to, $transition->action, 1, 2));

        return ProfessionalStatusTransportEnvelope::wrap(new ProfessionalStatusDeliveryPayload($event));
    }
}
