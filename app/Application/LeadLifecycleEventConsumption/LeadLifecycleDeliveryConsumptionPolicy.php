<?php

namespace App\Application\LeadLifecycleEventConsumption;

use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingDiagnostic;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingResult;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingStatus;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use LogicException;

final readonly class LeadLifecycleDeliveryConsumptionPolicy
{
    public function consumptionFor(LeadLifecycleEventRoutingResult $routing): PublicProjectionDeliveryConsumptionResult
    {
        return match ($routing->status) {
            LeadLifecycleEventRoutingStatus::Routed => PublicProjectionDeliveryConsumptionResult::Consumed,
            LeadLifecycleEventRoutingStatus::Deferred => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
            LeadLifecycleEventRoutingStatus::RetryableFailure => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
            LeadLifecycleEventRoutingStatus::Rejected => $this->rejectedConsumption($routing->diagnostic),
        };
    }

    private function rejectedConsumption(?LeadLifecycleEventRoutingDiagnostic $diagnostic): PublicProjectionDeliveryConsumptionResult
    {
        if ($diagnostic === LeadLifecycleEventRoutingDiagnostic::UnsupportedEvent) {
            return PublicProjectionDeliveryConsumptionResult::UnsupportedEventType;
        }
        if ($diagnostic === LeadLifecycleEventRoutingDiagnostic::CorruptedEvent) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        throw new LogicException('Rejected routing has an invalid diagnostic.');
    }
}
