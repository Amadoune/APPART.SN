<?php

namespace App\Application\ProfessionalStatusEventConsumption;

use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingDiagnostic;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingResult;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingStatus;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use LogicException;

final readonly class ProfessionalStatusDeliveryConsumptionPolicy
{
    public function consumptionFor(ProfessionalStatusEventRoutingResult $routing): PublicProjectionDeliveryConsumptionResult
    {
        return match ($routing->status) {
            ProfessionalStatusEventRoutingStatus::Routed => PublicProjectionDeliveryConsumptionResult::Consumed,
            ProfessionalStatusEventRoutingStatus::Deferred => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
            ProfessionalStatusEventRoutingStatus::RetryableFailure => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
            ProfessionalStatusEventRoutingStatus::Rejected => $this->rejectedConsumption($routing->diagnostic),
        };
    }

    private function rejectedConsumption(?ProfessionalStatusEventRoutingDiagnostic $diagnostic): PublicProjectionDeliveryConsumptionResult
    {
        if ($diagnostic === ProfessionalStatusEventRoutingDiagnostic::UnsupportedEvent) {
            return PublicProjectionDeliveryConsumptionResult::UnsupportedEventType;
        }
        if ($diagnostic === ProfessionalStatusEventRoutingDiagnostic::CorruptedEvent) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        throw new LogicException('Rejected professional status routing has an invalid diagnostic.');
    }
}
