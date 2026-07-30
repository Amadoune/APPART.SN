<?php

namespace App\Application\MediaItemLifecycleEventConsumption;

use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingDiagnostic;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingResult;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingStatus;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use LogicException;

final readonly class MediaItemLifecycleDeliveryConsumptionPolicy
{
    public function consumptionFor(MediaItemLifecycleEventRoutingResult $routing): PublicProjectionDeliveryConsumptionResult
    {
        return match ($routing->status) {
            MediaItemLifecycleEventRoutingStatus::Routed => PublicProjectionDeliveryConsumptionResult::Consumed,
            MediaItemLifecycleEventRoutingStatus::Deferred => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
            MediaItemLifecycleEventRoutingStatus::RetryableFailure => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
            MediaItemLifecycleEventRoutingStatus::Rejected => $this->rejectedConsumption($routing->diagnostic),
        };
    }

    private function rejectedConsumption(?MediaItemLifecycleEventRoutingDiagnostic $diagnostic): PublicProjectionDeliveryConsumptionResult
    {
        if ($diagnostic === MediaItemLifecycleEventRoutingDiagnostic::UnsupportedEvent) {
            return PublicProjectionDeliveryConsumptionResult::UnsupportedEventType;
        }
        if ($diagnostic === MediaItemLifecycleEventRoutingDiagnostic::CorruptedEvent) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        throw new LogicException('Rejected media item lifecycle routing has an invalid diagnostic.');
    }
}
