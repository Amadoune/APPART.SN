<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority;

enum CredentialVerifierStatus: string
{
    case Verified = 'Verified';
    case Rejected = 'Rejected';
    case DependencyUnavailable = 'DependencyUnavailable';
    case AccountUnavailable = 'AccountUnavailable';
}
