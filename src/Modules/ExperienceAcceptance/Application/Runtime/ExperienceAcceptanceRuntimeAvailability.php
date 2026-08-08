<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Runtime;

enum ExperienceAcceptanceRuntimeAvailability: string
{
    case Available = 'available';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
