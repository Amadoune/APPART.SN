<?php

namespace App\Application\RuntimeHealth;

enum RuntimeHealthStatus: string
{
    case Healthy = 'healthy';
    case Degraded = 'degraded';
    case Unavailable = 'unavailable';
}
