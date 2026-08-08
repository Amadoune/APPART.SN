<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead;

enum PerformanceReadinessStatusV1: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
