<?php

namespace App\Application\PublicSearchResults;

enum PublicSearchResultsStatus: string
{
    case Available = 'available';
    case Empty = 'empty';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
