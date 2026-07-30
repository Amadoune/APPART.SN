<?php

namespace App\Application\IdentityAccessEventOutbox\Contract;

use App\Application\IdentityAccessEventOutbox\IdentityAccessOutboxWriteResult;
use App\Application\IdentityAccessEventRouting\IdentityAccessRoutingDestination;
use App\Application\IdentityAccessEventTransport\IdentityAccessDeliveryMessageV1;

interface IdentityAccessOutboxWriter
{
    /** @param list<IdentityAccessRoutingDestination> $destinations */
    public function append(IdentityAccessDeliveryMessageV1 $message, array $destinations): IdentityAccessOutboxWriteResult;
}
