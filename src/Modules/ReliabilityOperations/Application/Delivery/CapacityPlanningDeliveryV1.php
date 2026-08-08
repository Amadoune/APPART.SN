<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\CapacityPlanningEventType;

final readonly class CapacityPlanningDeliveryV1
{
    public function __construct(public CapacityPlanningEventType $type, public CapacityPlanningDeliveryPayload $payload) {}
}
