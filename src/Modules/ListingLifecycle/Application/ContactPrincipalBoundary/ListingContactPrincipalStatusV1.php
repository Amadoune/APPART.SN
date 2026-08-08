<?php

namespace Appart\Modules\ListingLifecycle\Application\ContactPrincipalBoundary;

enum ListingContactPrincipalStatusV1: string
{
    case Resolved = 'resolved';
    case Missing = 'missing';
    case NotAssigned = 'not_assigned';
    case Ambiguous = 'ambiguous';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
