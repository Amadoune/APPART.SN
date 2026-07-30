<?php

namespace App\Application\IdentityAccessEventDelivery;

use App\Application\IdentityAccessEventRouting\IdentityAccessRoutingDestination;
use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventV1;

final readonly class IdentityAccessConsumedFact
{
    public function __construct(
        public string $messageId,
        public string $payloadChecksum,
        public IdentityAccessRoutingDestination $destination,
        public IdentityAccessEventV1 $event,
    ) {}
}
