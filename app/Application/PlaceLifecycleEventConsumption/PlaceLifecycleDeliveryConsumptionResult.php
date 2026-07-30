<?php

namespace App\Application\PlaceLifecycleEventConsumption;

enum PlaceLifecycleDeliveryConsumptionResult: string
{
    case Acknowledged = 'acknowledged';
    case Retry = 'retry';
    case Quarantined = 'quarantined';
}
