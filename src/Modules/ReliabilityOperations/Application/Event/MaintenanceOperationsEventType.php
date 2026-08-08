<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

enum MaintenanceOperationsEventType: string
{
    case Observed = 'reliability-operations.maintenance-operations.observed.v1';
}
