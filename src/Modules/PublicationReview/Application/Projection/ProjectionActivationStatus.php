<?php

namespace Appart\Modules\PublicationReview\Application\Projection;

enum ProjectionActivationStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case NotReady = 'not_ready';
    case Conflict = 'conflict';
    case DependencyUnavailable = 'dependency_unavailable';
}
