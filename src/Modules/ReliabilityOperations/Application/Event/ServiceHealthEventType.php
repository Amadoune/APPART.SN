<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

enum ServiceHealthEventType: string
{
    case Observed = 'reliability-operations.service-health.observed.v1';
}
