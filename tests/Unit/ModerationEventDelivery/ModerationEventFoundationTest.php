<?php

namespace Tests\Unit\ModerationEventDelivery;

use App\Application\ModerationEventRouting\DeterministicModerationEventRouter;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationEventFoundationTest extends TestCase
{
    #[Test]
    public function five_event_catalog_transport_and_owner_routes_are_closed(): void
    {
        self::assertCount(5, ModerationEventTypeV1::cases());
        foreach (ModerationEventTypeV1::cases() as $type) {
            $event = $this->event($type);
            $message = new ModerationDeliveryMessageV1($event);
            $serialized = (new ModerationEventTransportSerializer)->serialize($message);
            (new ModerationEventTransportSerializer)->validate($message, $serialized);
            self::assertNotEmpty((new DeterministicModerationEventRouter)->route($event));
            self::assertStringNotContainsString('email', $serialized);
        }
    }

    #[Test]
    public function routing_contains_only_owner_local_destinations(): void
    {
        foreach ((new DeterministicModerationEventRouter)->route($this->event(ModerationEventTypeV1::DecisionIssued)) as $destination) {
            self::assertContains($destination, ModerationRoutingDestination::cases());
            self::assertStringStartsWith('moderation.', $destination->value);
        }
    }

    #[Test]
    public function listing_handoff_route_is_static_and_exclusive_to_decision_issued(): void
    {
        $router = new DeterministicModerationEventRouter;

        foreach (ModerationEventTypeV1::cases() as $type) {
            self::assertSame(
                $type === ModerationEventTypeV1::DecisionIssued,
                in_array(ModerationRoutingDestination::ListingHandoff, $router->route($this->event($type)), true),
            );
        }
    }

    #[Test]
    public function routing_extension_does_not_change_event_or_message_identity(): void
    {
        $event = $this->event(ModerationEventTypeV1::DecisionIssued);
        $message = new ModerationDeliveryMessageV1($event);

        self::assertSame('2ea11fbf-26a0-54ba-b869-52d1da7ff4a8', $event->eventId);
        self::assertSame('2845f6ef45eb1694da0cc9da75a4497fc5277a4d227251f40edb80aca72815ae', $event->checksum);
        self::assertSame(
            'moderation-v1-0345882d4ca0fe9f34e021799b57437566a4c102dca92e71e0c63a7c8b482b51',
            $message->messageId,
        );
    }

    private function event(ModerationEventTypeV1 $type): ModerationEventV1
    {
        return new ModerationEventV1(
            $type, '53e00000-0000-4000-8000-000000000001', 1,
            ['decisionId' => '53e00000-0000-4000-8000-000000000002'],
            'v1', new DateTimeImmutable('2026-07-30T12:00:00+00:00'),
            new DateTimeImmutable('2026-07-30T12:00:00+00:00'),
            '53e00000-0000-4000-8000-000000000003',
            '53e00000-0000-4000-8000-000000000004',
        );
    }
}
