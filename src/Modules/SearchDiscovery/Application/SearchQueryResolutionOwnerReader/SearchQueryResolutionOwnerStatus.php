<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader;

enum SearchQueryResolutionOwnerStatus: string
{
    case Found = 'found';
    case Empty = 'empty';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
