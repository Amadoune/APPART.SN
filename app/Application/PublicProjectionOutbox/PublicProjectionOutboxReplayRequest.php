<?php

namespace App\Application\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;

final readonly class PublicProjectionOutboxReplayRequest
{
    public function __construct(
        public PublicProjectionOutboxCursorIdentity $cursorIdentity,
        public ?PublicProjectionDeliveryOrder $fromExclusive,
        public PublicProjectionDeliveryOrder $toInclusive,
    ) {}
}
