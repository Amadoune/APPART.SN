<?php

namespace Appart\Modules\Notifications\Application\Outbox;

use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryV1;

final readonly class NotificationOutboxResult
{
    public function __construct(
        public string $messageId,
        public NotificationOutboxStatus $status,
        public NotificationDeliveryV1 $delivery,
        public int $retryCount,
    ) {}
}
