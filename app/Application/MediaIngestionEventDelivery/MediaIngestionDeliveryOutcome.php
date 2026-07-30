<?php

namespace App\Application\MediaIngestionEventDelivery;

enum MediaIngestionDeliveryOutcome: string
{
    case Consumed = 'Consumed';
    case AlreadyConsumed = 'AlreadyConsumed';
    case TransientFailure = 'TransientFailure';
    case PermanentFailure = 'PermanentFailure';
    case DivergentReplay = 'DivergentReplay';
}
