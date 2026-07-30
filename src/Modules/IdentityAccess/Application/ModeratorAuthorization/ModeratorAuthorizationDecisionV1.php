<?php

namespace Appart\Modules\IdentityAccess\Application\ModeratorAuthorization;

enum ModeratorAuthorizationDecisionV1: string
{
    case Allowed = 'allowed';
    case Denied = 'denied';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
