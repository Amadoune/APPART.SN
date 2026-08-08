<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

enum ObservabilityEventStatus: string
{
    case Available = 'available';
    case Degraded = 'degraded';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
