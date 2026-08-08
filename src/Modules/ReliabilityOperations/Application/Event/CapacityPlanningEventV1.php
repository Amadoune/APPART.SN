<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

final readonly class CapacityPlanningEventV1
{
    public function __construct(public CapacityPlanningEventType $type, public CapacityPlanningEventPayload $payload) {}
}
