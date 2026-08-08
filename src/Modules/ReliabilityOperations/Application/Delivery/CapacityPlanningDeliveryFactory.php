<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\CapacityPlanningEventV1;

final readonly class CapacityPlanningDeliveryFactory
{
    public function create(CapacityPlanningEventV1 $event): CapacityPlanningDeliveryResult
    {
        return new CapacityPlanningDeliveryResult(new CapacityPlanningDeliveryV1($event->type, new CapacityPlanningDeliveryPayload(CapacityPlanningDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
