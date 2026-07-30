<?php

namespace App\Application\ModerationEventOutbox;

use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;

final readonly class ModerationOutboxDelivery
{
    public function __construct(
        public ModerationDeliveryMessageV1 $message,
        public ModerationRoutingDestination $destination,
        public int $attempt,
        public string $claimOwner,
    ) {}
}
