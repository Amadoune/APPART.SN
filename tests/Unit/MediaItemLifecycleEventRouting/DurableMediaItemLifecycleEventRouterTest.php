<?php

namespace Tests\Unit\MediaItemLifecycleEventRouting;

use App\Application\MediaItemLifecycleEventRouting\DurableMediaItemLifecycleEventRouter;
use App\Application\MediaItemLifecycleEventRouting\MediaItemLifecycleInboxStore;
use App\Application\MediaItemLifecycleEventRouting\MediaItemLifecycleInboxStoreResult;
use App\Application\MediaItemLifecycleEventRouting\MediaItemLifecycleInboxStoreStatus;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleDeliveryPayload;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingDiagnostic;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingStatus;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportEnvelope;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEvent;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventCatalog;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventId;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventMetadata;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayload;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayloadVersion;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DurableMediaItemLifecycleEventRouterTest extends TestCase
{
    #[DataProvider('storeMappings')]
    public function test_store_results_are_mapped_exhaustively(MediaItemLifecycleInboxStoreStatus $storeStatus, MediaItemLifecycleEventRoutingStatus $routingStatus, ?MediaItemLifecycleEventRoutingDiagnostic $diagnostic): void
    {
        $store = new class($storeStatus) implements MediaItemLifecycleInboxStore
        {
            public function __construct(private MediaItemLifecycleInboxStoreStatus $status) {}

            public function store(MediaItemLifecycleTransportEnvelope $envelope): MediaItemLifecycleInboxStoreResult
            {
                return new MediaItemLifecycleInboxStoreResult($this->status);
            }
        };

        $result = (new DurableMediaItemLifecycleEventRouter($store))->route($this->envelope());

        self::assertSame($routingStatus, $result->status);
        self::assertSame($diagnostic, $result->diagnostic);
        self::assertSame($routingStatus === MediaItemLifecycleEventRoutingStatus::Routed, $result->acknowledgesDelivery());
    }

    public function test_technical_exception_is_closed_retryable_failure(): void
    {
        $store = new class implements MediaItemLifecycleInboxStore
        {
            public function store(MediaItemLifecycleTransportEnvelope $envelope): MediaItemLifecycleInboxStoreResult
            {
                throw new RuntimeException('technical');
            }
        };

        $result = (new DurableMediaItemLifecycleEventRouter($store))->route($this->envelope());

        self::assertSame(MediaItemLifecycleEventRoutingStatus::RetryableFailure, $result->status);
        self::assertSame(MediaItemLifecycleEventRoutingDiagnostic::TransferFailed, $result->diagnostic);
    }

    /** @return list<array{MediaItemLifecycleInboxStoreStatus,MediaItemLifecycleEventRoutingStatus,?MediaItemLifecycleEventRoutingDiagnostic}> */
    public static function storeMappings(): array
    {
        return [
            [MediaItemLifecycleInboxStoreStatus::Stored, MediaItemLifecycleEventRoutingStatus::Routed, null],
            [MediaItemLifecycleInboxStoreStatus::AlreadyStored, MediaItemLifecycleEventRoutingStatus::Routed, null],
            [MediaItemLifecycleInboxStoreStatus::Unavailable, MediaItemLifecycleEventRoutingStatus::Deferred, MediaItemLifecycleEventRoutingDiagnostic::RouteUnavailable],
            [MediaItemLifecycleInboxStoreStatus::RetryableFailure, MediaItemLifecycleEventRoutingStatus::RetryableFailure, MediaItemLifecycleEventRoutingDiagnostic::TransferFailed],
            [MediaItemLifecycleInboxStoreStatus::Rejected, MediaItemLifecycleEventRoutingStatus::Rejected, MediaItemLifecycleEventRoutingDiagnostic::CorruptedEvent],
        ];
    }

    private function envelope(): MediaItemLifecycleTransportEnvelope
    {
        $transition = new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Removed, MediaItemLifecycleAction::Remove);
        $mediaId = MediaItemLifecycleId::fromString('a4100000-0000-4000-8000-000000000099');
        $version = MediaItemLifecycleEventPayloadVersion::V1;
        $type = (new MediaItemLifecycleEventCatalog)->typeFor($transition);
        $eventId = MediaItemLifecycleEventId::derive($type, $version, $mediaId, $transition, 2);
        $at = MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));
        $event = new MediaItemLifecycleEvent(
            new MediaItemLifecycleEventMetadata($type, $version, MediaItemLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at),
            new MediaItemLifecycleEventPayload($eventId, $mediaId, 'active>remove>removed', $transition->from, $transition->to, $transition->action, 1, 2),
        );

        return MediaItemLifecycleTransportEnvelope::wrap(new MediaItemLifecycleDeliveryPayload($event));
    }
}
