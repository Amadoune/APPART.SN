<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicFacts;

enum PublicFactHandoffResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentIntent = 'divergent_intent';
    case VersionConflict = 'version_conflict';
    case MissingCandidate = 'missing_candidate';
    case DependencyUnavailable = 'dependency_unavailable';
}
