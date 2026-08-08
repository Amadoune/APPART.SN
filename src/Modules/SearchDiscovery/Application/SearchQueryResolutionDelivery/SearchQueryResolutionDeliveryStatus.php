<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery;

enum SearchQueryResolutionDeliveryStatus: string
{
    case Found = 'found';
    case Empty = 'empty';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
