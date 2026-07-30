<?php

namespace App\Application\AdministrativeActionLifecycleEventConsumption;

use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingDiagnostic;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingResult;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingStatus;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use LogicException;

final readonly class AdministrativeActionLifecycleDeliveryConsumptionPolicy
{
    public function consumptionFor(AdministrativeActionLifecycleEventRoutingResult $routing): PublicProjectionDeliveryConsumptionResult
    {
        return match ($routing->status) {
            AdministrativeActionLifecycleEventRoutingStatus::Routed => PublicProjectionDeliveryConsumptionResult::Consumed,
            AdministrativeActionLifecycleEventRoutingStatus::Deferred => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
            AdministrativeActionLifecycleEventRoutingStatus::RetryableFailure => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
            AdministrativeActionLifecycleEventRoutingStatus::Rejected => $this->rejectedConsumption($routing->diagnostic),
        };
    }

    private function rejectedConsumption(?AdministrativeActionLifecycleEventRoutingDiagnostic $diagnostic): PublicProjectionDeliveryConsumptionResult
    {
        if ($diagnostic === AdministrativeActionLifecycleEventRoutingDiagnostic::UnsupportedEvent) {
            return PublicProjectionDeliveryConsumptionResult::UnsupportedEventType;
        }
        if ($diagnostic === AdministrativeActionLifecycleEventRoutingDiagnostic::CorruptedEvent) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        throw new LogicException('Rejected Administrative Action Lifecycle routing has an invalid diagnostic.');
    }
}
