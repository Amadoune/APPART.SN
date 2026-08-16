<?php

namespace Appart\Modules\Geography\Application\GeographySelection;

enum GeographySelectionStatus: string
{
    case Available = 'available';
    case Empty = 'empty';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
