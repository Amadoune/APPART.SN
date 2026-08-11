<?php

namespace Appart\Modules\PublicationReview\Application\Review;

enum PublicationReviewCommandStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case VersionConflict = 'version_conflict';
    case StateConflict = 'state_conflict';
    case Missing = 'missing';
    case DivergentCommand = 'divergent_command';
    case GatewayRejected = 'gateway_rejected';
    case DependencyUnavailable = 'dependency_unavailable';
}
