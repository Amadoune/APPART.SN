<?php

namespace Tests\Unit\LeadLifecycleEventRouting;

use App\Application\LeadLifecycleEventRouting\DurableLeadLifecycleEventRouter;
use App\Application\LeadLifecycleEventRouting\LeadLifecycleInboxStore;
use App\Application\LeadLifecycleEventRouting\LeadLifecycleInboxStoreResult;
use App\Application\LeadLifecycleEventRouting\LeadLifecycleInboxStoreStatus;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleDeliveryPayload;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingDiagnostic;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingStatus;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportEnvelope;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEvent;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventMetadata;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayload;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayloadVersion;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventType;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DurableLeadLifecycleEventRouterTest extends TestCase
{
    #[DataProvider('storeMappings')]
    public function test_store_results_are_mapped_exhaustively(
        LeadLifecycleInboxStoreStatus $storeStatus,
        LeadLifecycleEventRoutingStatus $routingStatus,
        ?LeadLifecycleEventRoutingDiagnostic $diagnostic,
    ): void {
        $store = new class($storeStatus) implements LeadLifecycleInboxStore
        {
            public function __construct(private LeadLifecycleInboxStoreStatus $status) {}

            public function store(LeadLifecycleTransportEnvelope $envelope): LeadLifecycleInboxStoreResult
            {
                return new LeadLifecycleInboxStoreResult($this->status);
            }
        };

        $result = (new DurableLeadLifecycleEventRouter($store))->route($this->envelope());

        self::assertSame($routingStatus, $result->status);
        self::assertSame($diagnostic, $result->diagnostic);
        self::assertSame($routingStatus === LeadLifecycleEventRoutingStatus::Routed, $result->acknowledgesDelivery());
    }

    public function test_technical_exception_is_a_closed_retryable_failure(): void
    {
        $store = new class implements LeadLifecycleInboxStore
        {
            public function store(LeadLifecycleTransportEnvelope $envelope): LeadLifecycleInboxStoreResult
            {
                throw new \RuntimeException('technical');
            }
        };

        $result = (new DurableLeadLifecycleEventRouter($store))->route($this->envelope());

        self::assertSame(LeadLifecycleEventRoutingStatus::RetryableFailure, $result->status);
        self::assertSame(LeadLifecycleEventRoutingDiagnostic::TransferFailed, $result->diagnostic);
    }

    /** @return list<array{LeadLifecycleInboxStoreStatus, LeadLifecycleEventRoutingStatus, ?LeadLifecycleEventRoutingDiagnostic}> */
    public static function storeMappings(): array
    {
        return [
            [LeadLifecycleInboxStoreStatus::Stored, LeadLifecycleEventRoutingStatus::Routed, null],
            [LeadLifecycleInboxStoreStatus::AlreadyStored, LeadLifecycleEventRoutingStatus::Routed, null],
            [LeadLifecycleInboxStoreStatus::Unavailable, LeadLifecycleEventRoutingStatus::Deferred, LeadLifecycleEventRoutingDiagnostic::RouteUnavailable],
            [LeadLifecycleInboxStoreStatus::RetryableFailure, LeadLifecycleEventRoutingStatus::RetryableFailure, LeadLifecycleEventRoutingDiagnostic::TransferFailed],
            [LeadLifecycleInboxStoreStatus::Rejected, LeadLifecycleEventRoutingStatus::Rejected, LeadLifecycleEventRoutingDiagnostic::CorruptedEvent],
        ];
    }

    private function envelope(): LeadLifecycleTransportEnvelope
    {
        $transition = new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver);
        $leadId = LeadId::fromString('a4100000-0000-4000-8000-000000000098');
        $version = LeadLifecycleEventPayloadVersion::V1;
        $eventId = LeadLifecycleEventId::derive(LeadLifecycleEventType::Delivered, $version, $leadId, $transition, 2);
        $at = LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));
        $event = new LeadLifecycleEvent(
            new LeadLifecycleEventMetadata(LeadLifecycleEventType::Delivered, $version, LeadLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at),
            new LeadLifecycleEventPayload($eventId, $leadId, 'created>deliver>delivered', $transition->from, $transition->to, $transition->action, 1, 2),
        );

        return LeadLifecycleTransportEnvelope::wrap(new LeadLifecycleDeliveryPayload($event));
    }
}
