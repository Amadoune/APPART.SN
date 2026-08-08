<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

enum ObservabilityEventType: string
{
    case Observed = 'reliability-operations.observability.observed.v1';
}
