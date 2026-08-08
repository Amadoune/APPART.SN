<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

enum ReleaseCandidateEventStatus: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
