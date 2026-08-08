<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent;

enum SearchQueryResolutionEventStatus: string
{
    case Found = 'found';
    case Empty = 'empty';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
