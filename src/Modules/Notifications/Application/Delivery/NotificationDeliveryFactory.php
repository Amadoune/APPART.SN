<?php

namespace Appart\Modules\Notifications\Application\Delivery;

use Appart\Modules\Notifications\Application\Event\NotificationEventStatus;
use Appart\Modules\Notifications\Application\Event\NotificationEventV1;

final readonly class NotificationDeliveryFactory
{
    public function create(NotificationEventV1 $event): NotificationDeliveryResult
    {
        $status = match ($event->payload->status) {
            NotificationEventStatus::Enabled => NotificationDeliveryStatus::Enabled,
            NotificationEventStatus::Disabled => NotificationDeliveryStatus::Disabled,
            NotificationEventStatus::Available => NotificationDeliveryStatus::Available,
            NotificationEventStatus::Allowed => NotificationDeliveryStatus::Allowed,
            NotificationEventStatus::Blocked => NotificationDeliveryStatus::Blocked,
            NotificationEventStatus::Missing => NotificationDeliveryStatus::Missing,
            NotificationEventStatus::Corrupted => NotificationDeliveryStatus::Corrupted,
            NotificationEventStatus::DependencyUnavailable => NotificationDeliveryStatus::DependencyUnavailable,
        };

        return new NotificationDeliveryResult(new NotificationDeliveryV1(new NotificationDeliveryPayload($event->type, $status, $event->payload->observedAt)));
    }
}
