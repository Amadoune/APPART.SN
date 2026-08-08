<?php

namespace Tests\Unit\Notifications\Delivery;

use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryFactory;
use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryStatus;
use Appart\Modules\Notifications\Application\Event\NotificationEventPayload;
use Appart\Modules\Notifications\Application\Event\NotificationEventStatus;
use Appart\Modules\Notifications\Application\Event\NotificationEventType;
use Appart\Modules\Notifications\Application\Event\NotificationEventV1;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NotificationDeliveryFactoryTest extends TestCase
{
    #[DataProvider('events')]
    public function test_each_event_produces_exactly_one_delivery(NotificationEventType $type, NotificationEventStatus $status): void
    {
        $event = new NotificationEventV1($type, new NotificationEventPayload($status, '2026-08-02T12:00:00.123456Z'));
        $result = (new NotificationDeliveryFactory)->create($event);
        self::assertSame(NotificationDeliveryStatus::from($status->value), $result->status());
        self::assertSame(['type' => $type->value, 'status' => $status->value, 'observedAt' => '2026-08-02T12:00:00.123456Z'], $result->delivery->payload->canonical());
    }

    /** @return iterable<string, array{NotificationEventType, NotificationEventStatus}> */
    public static function events(): iterable
    {
        $allowed = [
            [NotificationEventType::PreferenceObserved, [NotificationEventStatus::Enabled, NotificationEventStatus::Disabled, NotificationEventStatus::Missing, NotificationEventStatus::Corrupted, NotificationEventStatus::DependencyUnavailable]],
            [NotificationEventType::TemplateObserved, [NotificationEventStatus::Available, NotificationEventStatus::Missing, NotificationEventStatus::Corrupted, NotificationEventStatus::DependencyUnavailable]],
            [NotificationEventType::ChannelObserved, [NotificationEventStatus::Allowed, NotificationEventStatus::Blocked, NotificationEventStatus::Missing, NotificationEventStatus::Corrupted, NotificationEventStatus::DependencyUnavailable]],
        ];
        foreach ($allowed as [$type,$statuses]) {
            foreach ($statuses as $status) {
                yield $type->value.' '.$status->value => [$type, $status];
            }
        }
    }
}
