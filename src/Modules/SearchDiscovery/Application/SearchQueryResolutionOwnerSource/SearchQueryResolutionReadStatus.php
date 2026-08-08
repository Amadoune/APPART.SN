<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource;

enum SearchQueryResolutionReadStatus: string
{
    case Found = 'found';
    case Empty = 'empty';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
