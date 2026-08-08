<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

enum CapacityPlanningEventType: string
{
    case Observed = 'reliability-operations.capacity-planning.observed.v1';
}
