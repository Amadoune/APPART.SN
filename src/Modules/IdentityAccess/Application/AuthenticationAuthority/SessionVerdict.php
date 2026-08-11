<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

enum SessionVerdict: string
{
    case Valid = 'Valid';
    case RotationRequired = 'RotationRequired';
    case Expired = 'Expired';
    case Revoked = 'Revoked';
    case InvalidSecret = 'InvalidSecret';
    case Missing = 'Missing';
    case DependencyUnavailable = 'DependencyUnavailable';
}
