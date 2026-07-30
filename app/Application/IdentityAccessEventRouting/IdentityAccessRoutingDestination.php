<?php

namespace App\Application\IdentityAccessEventRouting;

enum IdentityAccessRoutingDestination: string
{
    case PrivateAudit = 'identity_access.private_audit';
    case IdentitySource = 'identity_access.identity_source';
    case Notifications = 'identity_access.notifications';
    case SessionInvalidation = 'identity_access.session_invalidation';
    case CrossDomainAvailability = 'identity_access.cross_domain_availability';
}
