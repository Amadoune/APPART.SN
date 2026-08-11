<?php

namespace Appart\Modules\PublicationReview\Application\Queue;

enum PublicationReviewClaimStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case VersionConflict = 'version_conflict';
    case Conflict = 'conflict';
    case Missing = 'missing';
    case DivergentCommand = 'divergent_command';
    case DependencyUnavailable = 'dependency_unavailable';
}
