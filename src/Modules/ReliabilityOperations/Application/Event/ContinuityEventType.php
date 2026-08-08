<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

enum ContinuityEventType: string
{
    case Observed = 'reliability-operations.continuity.observed.v1';
}
