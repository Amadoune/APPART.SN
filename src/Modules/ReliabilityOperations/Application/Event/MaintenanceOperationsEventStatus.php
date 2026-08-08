<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

enum MaintenanceOperationsEventStatus: string
{
    case Ready = 'ready';
    case Degraded = 'degraded';
    case Blocked = 'blocked';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
