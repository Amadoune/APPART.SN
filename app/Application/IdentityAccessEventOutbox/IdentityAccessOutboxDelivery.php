<?php

namespace App\Application\IdentityAccessEventOutbox;

use App\Application\IdentityAccessEventRouting\IdentityAccessRoutingDestination;
use App\Application\IdentityAccessEventTransport\IdentityAccessDeliveryMessageV1;

final readonly class IdentityAccessOutboxDelivery
{
    public function __construct(
        public IdentityAccessDeliveryMessageV1 $message,
        public IdentityAccessRoutingDestination $destination,
        public int $attempt,
        public string $claimOwner,
    ) {}
}
