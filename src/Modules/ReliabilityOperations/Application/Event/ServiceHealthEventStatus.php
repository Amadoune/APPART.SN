<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

enum ServiceHealthEventStatus: string
{
    case Healthy = 'healthy';
    case Degraded = 'degraded';
    case Unavailable = 'unavailable';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
