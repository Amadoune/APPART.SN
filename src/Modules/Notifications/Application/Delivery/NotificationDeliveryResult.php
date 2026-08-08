<?php

namespace Appart\Modules\Notifications\Application\Delivery;

final readonly class NotificationDeliveryResult
{
    public function __construct(public NotificationDeliveryV1 $delivery) {}

    public function status(): NotificationDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
