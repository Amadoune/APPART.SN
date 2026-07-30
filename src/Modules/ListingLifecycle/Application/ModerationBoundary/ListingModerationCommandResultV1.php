<?php

namespace Appart\Modules\ListingLifecycle\Application\ModerationBoundary;

enum ListingModerationCommandResultV1: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case Rejected = 'rejected';
    case DivergentIntent = 'divergent_intent';
    case VersionConflict = 'version_conflict';
    case DependencyUnavailable = 'dependency_unavailable';
}
