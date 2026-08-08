<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

enum ServiceHealthDeliveryStatus: string
{
    case Healthy = 'healthy';
    case Degraded = 'degraded';
    case Unavailable = 'unavailable';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
