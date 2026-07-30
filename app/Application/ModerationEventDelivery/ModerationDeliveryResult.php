<?php

namespace App\Application\ModerationEventDelivery;

enum ModerationDeliveryResult: string
{
    case Delivered = 'delivered';
    case AlreadyDelivered = 'already_delivered';
    case DivergentDelivery = 'divergent_delivery';
    case Retry = 'retry';
    case Quarantined = 'quarantined';
    case Rejected = 'rejected';
}
