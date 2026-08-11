<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

enum LoginIdentityResolutionStatus: string
{
    case Resolved = 'Resolved';
    case NotResolved = 'NotResolved';
    case DependencyUnavailable = 'DependencyUnavailable';
}
