<?php

namespace App\Application\PublicationReviewExperience;

enum PublicationReviewExperienceStatus: string
{
    case Available = 'available';
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case Empty = 'empty';
    case Forbidden = 'forbidden';
    case NotFound = 'not_found';
    case Conflict = 'conflict';
    case NotReady = 'not_ready';
    case DependencyUnavailable = 'dependency_unavailable';
}
