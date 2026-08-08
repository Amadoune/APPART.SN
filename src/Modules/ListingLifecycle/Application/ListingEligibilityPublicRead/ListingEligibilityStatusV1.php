<?php

namespace Appart\Modules\ListingLifecycle\Application\ListingEligibilityPublicRead;

enum ListingEligibilityStatusV1: string
{
    case Eligible = 'eligible';
    case NotEligible = 'not_eligible';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
