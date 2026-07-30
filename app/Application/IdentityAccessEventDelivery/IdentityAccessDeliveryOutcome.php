<?php

namespace App\Application\IdentityAccessEventDelivery;

enum IdentityAccessDeliveryOutcome: string
{
    case Consumed = 'consumed';
    case AlreadyConsumed = 'already_consumed';
    case TransientFailure = 'transient_failure';
    case PermanentFailure = 'permanent_failure';
    case DivergentReplay = 'divergent_replay';
}
