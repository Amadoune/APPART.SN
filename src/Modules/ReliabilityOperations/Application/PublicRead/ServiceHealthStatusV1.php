<?php

namespace Appart\Modules\ReliabilityOperations\Application\PublicRead;

enum ServiceHealthStatusV1: string
{
    case Healthy = 'healthy';
    case Degraded = 'degraded';
    case Unavailable = 'unavailable';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
