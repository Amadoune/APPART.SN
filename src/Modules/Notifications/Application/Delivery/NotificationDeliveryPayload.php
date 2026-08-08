<?php

namespace Appart\Modules\Notifications\Application\Delivery;

use Appart\Modules\Notifications\Application\Event\NotificationEventType;

final readonly class NotificationDeliveryPayload
{
    public function __construct(public NotificationEventType $type, public NotificationDeliveryStatus $status, public string $observedAt) {}

    /** @return array{type:string, status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['type' => $this->type->value, 'status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
