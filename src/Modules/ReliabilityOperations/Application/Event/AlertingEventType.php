<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

enum AlertingEventType: string
{
    case Observed = 'reliability-operations.alerting.observed.v1';
}
