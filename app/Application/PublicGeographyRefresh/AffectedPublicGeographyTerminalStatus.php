<?php

namespace App\Application\PublicGeographyRefresh;

enum AffectedPublicGeographyTerminalStatus: string
{
    case Available = 'available';
    case Empty = 'empty';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
