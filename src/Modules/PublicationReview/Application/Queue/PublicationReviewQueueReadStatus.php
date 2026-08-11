<?php

namespace Appart\Modules\PublicationReview\Application\Queue;

enum PublicationReviewQueueReadStatus: string
{
    case Available = 'available';
    case Empty = 'empty';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
