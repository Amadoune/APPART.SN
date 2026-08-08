<?php

namespace Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary;

enum ListingContactabilityDecisionV1: string
{
    case Contactable = 'contactable';
    case NotContactable = 'not_contactable';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
