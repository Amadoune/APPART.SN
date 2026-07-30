<?php

namespace Tests\Unit\AdministrationAudit;

use App\Application\AdministrativeActionLifecycleEventRouting\AdministrativeActionLifecycleInboxStore;
use App\Application\AdministrativeActionLifecycleEventRouting\AdministrativeActionLifecycleInboxStoreResult;
use App\Application\AdministrativeActionLifecycleEventRouting\AdministrativeActionLifecycleInboxStoreStatus;
use App\Application\AdministrativeActionLifecycleEventRouting\DurableAdministrativeActionLifecycleEventRouter;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleDeliveryPayload;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingDiagnostic;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingStatus;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleTransportEnvelope;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEvent;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventId;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventMetadata;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayload;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayloadVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventType;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DurableAdministrativeActionLifecycleEventRouterTest extends TestCase
{
    #[DataProvider('storeMappings')]
    public function test_store_results_are_mapped_exhaustively(
        AdministrativeActionLifecycleInboxStoreStatus $storeStatus,
        AdministrativeActionLifecycleEventRoutingStatus $routingStatus,
        ?AdministrativeActionLifecycleEventRoutingDiagnostic $diagnostic,
    ): void {
        $store = new class($storeStatus) implements AdministrativeActionLifecycleInboxStore
        {
            public function __construct(private AdministrativeActionLifecycleInboxStoreStatus $status) {}

            public function store(AdministrativeActionLifecycleTransportEnvelope $envelope): AdministrativeActionLifecycleInboxStoreResult
            {
                return new AdministrativeActionLifecycleInboxStoreResult($this->status);
            }
        };

        $result = (new DurableAdministrativeActionLifecycleEventRouter($store))->route($this->envelope());

        self::assertSame($routingStatus, $result->status);
        self::assertSame($diagnostic, $result->diagnostic);
        self::assertSame($routingStatus === AdministrativeActionLifecycleEventRoutingStatus::Routed, $result->acknowledgesDelivery());
    }

    public function test_technical_exception_is_closed_retryable_failure(): void
    {
        $store = new class implements AdministrativeActionLifecycleInboxStore
        {
            public function store(AdministrativeActionLifecycleTransportEnvelope $envelope): AdministrativeActionLifecycleInboxStoreResult
            {
                throw new RuntimeException('technical');
            }
        };

        $result = (new DurableAdministrativeActionLifecycleEventRouter($store))->route($this->envelope());

        self::assertSame(AdministrativeActionLifecycleEventRoutingStatus::RetryableFailure, $result->status);
        self::assertSame(AdministrativeActionLifecycleEventRoutingDiagnostic::TransferFailed, $result->diagnostic);
        self::assertFalse($result->acknowledgesDelivery());
    }

    /** @return list<array{AdministrativeActionLifecycleInboxStoreStatus,AdministrativeActionLifecycleEventRoutingStatus,?AdministrativeActionLifecycleEventRoutingDiagnostic}> */
    public static function storeMappings(): array
    {
        return [
            [AdministrativeActionLifecycleInboxStoreStatus::Stored, AdministrativeActionLifecycleEventRoutingStatus::Routed, null],
            [AdministrativeActionLifecycleInboxStoreStatus::AlreadyStored, AdministrativeActionLifecycleEventRoutingStatus::Routed, null],
            [AdministrativeActionLifecycleInboxStoreStatus::Unavailable, AdministrativeActionLifecycleEventRoutingStatus::Deferred, AdministrativeActionLifecycleEventRoutingDiagnostic::RouteUnavailable],
            [AdministrativeActionLifecycleInboxStoreStatus::RetryableFailure, AdministrativeActionLifecycleEventRoutingStatus::RetryableFailure, AdministrativeActionLifecycleEventRoutingDiagnostic::TransferFailed],
            [AdministrativeActionLifecycleInboxStoreStatus::Rejected, AdministrativeActionLifecycleEventRoutingStatus::Rejected, AdministrativeActionLifecycleEventRoutingDiagnostic::CorruptedEvent],
        ];
    }

    private function envelope(): AdministrativeActionLifecycleTransportEnvelope
    {
        $transition = new AdministrativeActionLifecycleTransition(
            AdministrativeActionLifecycleState::Draft,
            AdministrativeActionLifecycleState::Recorded,
            AdministrativeActionLifecycleAction::Record,
        );
        $actionId = AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000047');
        $version = AdministrativeActionLifecycleEventPayloadVersion::V1;
        $eventId = AdministrativeActionLifecycleEventId::derive(
            AdministrativeActionLifecycleEventType::Recorded,
            $version,
            $actionId,
            $transition,
            2,
        );
        $at = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T12:00:00+00:00'));
        $event = new AdministrativeActionLifecycleEvent(
            new AdministrativeActionLifecycleEventMetadata(AdministrativeActionLifecycleEventType::Recorded, $version, ActorId::fromString('decision-actor-001'), $at, $at),
            new AdministrativeActionLifecycleEventPayload($eventId, $actionId, 'draft>record>recorded', $transition->from, $transition->to, $transition->action, 1, 2),
        );

        return AdministrativeActionLifecycleTransportEnvelope::wrap(new AdministrativeActionLifecycleDeliveryPayload($event));
    }
}
