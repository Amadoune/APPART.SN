<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

final readonly class CapacityPlanningDeliveryResult
{
    public function __construct(public CapacityPlanningDeliveryV1 $delivery) {}

    public function status(): CapacityPlanningDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
