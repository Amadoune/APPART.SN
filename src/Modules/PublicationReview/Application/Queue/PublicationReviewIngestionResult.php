<?php

namespace Appart\Modules\PublicationReview\Application\Queue;

enum PublicationReviewIngestionResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case Ignored = 'ignored';
    case DivergentMessage = 'divergent_message';
    case DependencyUnavailable = 'dependency_unavailable';
}
