<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

enum OperationalReadinessEventType: string
{
    case Observed = 'reliability-operations.operational-readiness.observed.v1';
}
