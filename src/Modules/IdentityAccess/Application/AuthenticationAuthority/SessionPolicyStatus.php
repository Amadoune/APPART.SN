<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

enum SessionPolicyStatus: string
{
    case Valid = 'Valid';
    case RotationRequired = 'RotationRequired';
    case IdleExpired = 'IdleExpired';
    case AbsoluteExpired = 'AbsoluteExpired';
    case Revoked = 'Revoked';
    case DependencyUnavailable = 'DependencyUnavailable';
}
