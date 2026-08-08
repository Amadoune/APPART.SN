<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead;

enum MaintenanceOperationsStatusV1: string
{
    case Ready = 'ready';
    case Degraded = 'degraded';
    case Blocked = 'blocked';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
