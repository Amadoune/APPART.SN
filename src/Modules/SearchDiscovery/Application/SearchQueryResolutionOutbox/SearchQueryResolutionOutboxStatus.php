<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOutbox;

enum SearchQueryResolutionOutboxStatus: string
{
    case Found = 'found';
    case Empty = 'empty';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
