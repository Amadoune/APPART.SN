<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime;

enum IdentityAccessRuntimeHealthStatus: string
{
    case Healthy = 'Healthy';
    case Unhealthy = 'Unhealthy';
}
