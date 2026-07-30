<?php

namespace App\Application\ReservationLifecycleEventConsumer;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingResult;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingStatus;

final readonly class ReservationLifecycleDeliveryConsumptionPolicy
{
    public function consumptionFor(ReservationLifecycleRoutingResult $routing): PublicProjectionDeliveryConsumptionResult
    {
        return match ($routing->status) {
            ReservationLifecycleRoutingStatus::Stored => PublicProjectionDeliveryConsumptionResult::Consumed,
            ReservationLifecycleRoutingStatus::AlreadyStored => PublicProjectionDeliveryConsumptionResult::AlreadyConsumed,
            ReservationLifecycleRoutingStatus::CorruptedEnvelope => PublicProjectionDeliveryConsumptionResult::DivergentPayload,
            ReservationLifecycleRoutingStatus::PersistenceCorrupted => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
        };
    }
}
