<?php

namespace App\Application\PropertyAuthoringGeographySelection;

enum GeographySelectionReplayStatus: string
{
    case Validated = 'validated';
    case Invalid = 'invalid';
    case DependencyUnavailable = 'dependency_unavailable';
}
