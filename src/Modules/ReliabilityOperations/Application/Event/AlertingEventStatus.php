<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

enum AlertingEventStatus: string
{
    case Ready = 'ready';
    case Degraded = 'degraded';
    case Unavailable = 'unavailable';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
