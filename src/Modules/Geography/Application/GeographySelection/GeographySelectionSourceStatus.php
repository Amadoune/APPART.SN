<?php

namespace Appart\Modules\Geography\Application\GeographySelection;

enum GeographySelectionSourceStatus: string
{
    case Available = 'available';
    case Empty = 'empty';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
