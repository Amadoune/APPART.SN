<?php

namespace App\Application\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;

final readonly class PublicProjectionOutboxCursor
{
    public function __construct(
        public PublicProjectionOutboxCursorIdentity $identity,
        public ?PublicProjectionDeliveryOrder $progress,
        public ?PublicProjectionDeliveryOrder $highWatermark,
        public ?PublicProjectionDeliveryMessageId $lastMessageId,
    ) {}
}
