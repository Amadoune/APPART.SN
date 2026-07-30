<?php

namespace Tests\Unit\ReservationLifecycleEventTransport;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleDeliveryPayload;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEvent;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventCatalog;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventType;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use PHPUnit\Framework\TestCase;

final class ReservationLifecycleOutboxCompatibilityTest extends TestCase
{
    public function test_catalog_accepts_exactly_the_eleven_reservation_event_types(): void
    {
        $catalog = new PublicProjectionDeliveryEventCatalog;
        $payload = new ReservationLifecycleDeliveryPayload($this->event());

        foreach (ReservationLifecycleEventType::cases() as $type) {
            self::assertTrue($catalog->accepts(
                PublicProjectionDeliveryEventType::fromString($type->value),
                PublicProjectionDeliveryPayloadVersion::fromInt(1),
                PublicProjectionDeliverySourceModule::fromString('ReservationLifecycle'),
                PublicProjectionDeliveryAggregateType::fromString('ReservationLifecycle'),
                $payload,
            ));
        }
        self::assertCount(11, ReservationLifecycleEventType::cases());
    }

    public function test_catalog_rejects_wrong_module_aggregate_version_or_payload(): void
    {
        $catalog = new PublicProjectionDeliveryEventCatalog;
        $payload = new ReservationLifecycleDeliveryPayload($this->event());
        $type = PublicProjectionDeliveryEventType::fromString('reservation.lifecycle.submitted');

        self::assertFalse($catalog->accepts($type, PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'), PublicProjectionDeliveryAggregateType::fromString('ReservationLifecycle'), $payload));
        self::assertFalse($catalog->accepts($type, PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('ReservationLifecycle'), PublicProjectionDeliveryAggregateType::fromString('Listing'), $payload));
        self::assertFalse($catalog->accepts($type, PublicProjectionDeliveryPayloadVersion::fromInt(2), PublicProjectionDeliverySourceModule::fromString('ReservationLifecycle'), PublicProjectionDeliveryAggregateType::fromString('ReservationLifecycle'), $payload));
    }

    private function event(): ReservationLifecycleEvent
    {
        return (new ReservationLifecycleEventCatalog)->eventFor(
            ReservationId::fromString('22222222-2222-4222-8222-222222222222'),
            new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit),
            2,
        );
    }
}
