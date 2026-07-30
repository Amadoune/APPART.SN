<?php

namespace App\Application\PlaceLifecycleEventConsumption;

use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRoutingStatus;

final readonly class PlaceLifecycleDeliveryConsumptionPolicy
{
    public function consumptionFor(
        PlaceLifecycleEventRoutingStatus $status,
    ): PlaceLifecycleDeliveryConsumptionResult {
        return match ($status) {
            PlaceLifecycleEventRoutingStatus::Routed => PlaceLifecycleDeliveryConsumptionResult::Acknowledged,
            PlaceLifecycleEventRoutingStatus::Deferred,
            PlaceLifecycleEventRoutingStatus::RetryableFailure => PlaceLifecycleDeliveryConsumptionResult::Retry,
            PlaceLifecycleEventRoutingStatus::Rejected => PlaceLifecycleDeliveryConsumptionResult::Quarantined,
        };
    }
}
