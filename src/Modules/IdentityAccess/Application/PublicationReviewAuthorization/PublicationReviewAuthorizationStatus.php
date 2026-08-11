<?php

namespace Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization;

enum PublicationReviewAuthorizationStatus: string
{
    case Allowed = 'allowed';
    case Denied = 'denied';
    case DependencyUnavailable = 'dependency_unavailable';
}
