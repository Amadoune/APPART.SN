<?php

namespace App\Application\AuthoringDraftResume;

enum AuthoringDraftResumeStatus: string
{
    case Available = 'available';
    case NotFoundOrForbidden = 'not_found_or_forbidden';
    case Incomplete = 'incomplete';
    case StateConflict = 'state_conflict';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
