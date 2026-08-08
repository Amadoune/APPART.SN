<?php

namespace Appart\Modules\Notifications\Application\Outbox;

use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryV1;

final readonly class NotificationOutboxPolicy
{
    public const MAX_RETRIES = 10;

    public function prepare(NotificationDeliveryV1 $delivery): NotificationOutboxResult
    {
        return new NotificationOutboxResult($this->messageId($delivery), NotificationOutboxStatus::Applied, $delivery, 0);
    }

    public function messageId(NotificationDeliveryV1 $delivery): string
    {
        return hash('sha256', $this->canonical($delivery));
    }

    public function checksum(NotificationDeliveryV1 $delivery): string
    {
        return hash('sha256', "notifications-outbox-v1\n".$this->canonical($delivery));
    }

    public function canonical(NotificationDeliveryV1 $delivery): string
    {
        return json_encode($delivery->payload->canonical(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }
}
