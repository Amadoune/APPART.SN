<?php

namespace App\Application\IdentityAccessEventDelivery;

enum IdentityAccessRetryDecision: string
{
    case Complete = 'complete';
    case Retry = 'retry';
    case Quarantine = 'quarantine';
}
