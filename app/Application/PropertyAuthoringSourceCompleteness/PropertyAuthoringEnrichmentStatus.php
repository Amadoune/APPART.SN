<?php

namespace App\Application\PropertyAuthoringSourceCompleteness;

enum PropertyAuthoringEnrichmentStatus: string
{
    case Validated = 'validated';
    case Invalid = 'invalid';
    case DependencyUnavailable = 'dependency_unavailable';
}
