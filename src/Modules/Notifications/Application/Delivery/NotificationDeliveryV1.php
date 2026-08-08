<?php

namespace Appart\Modules\Notifications\Application\Delivery;

final readonly class NotificationDeliveryV1
{
    public const TYPE = 'notifications.delivery.v1';

    public function __construct(public NotificationDeliveryPayload $payload) {}
}
