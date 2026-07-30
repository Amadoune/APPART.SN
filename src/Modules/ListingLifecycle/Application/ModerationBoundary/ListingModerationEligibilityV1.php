<?php

namespace Appart\Modules\ListingLifecycle\Application\ModerationBoundary;

enum ListingModerationEligibilityV1: string
{
    case Eligible = 'eligible';
    case Ineligible = 'ineligible';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
