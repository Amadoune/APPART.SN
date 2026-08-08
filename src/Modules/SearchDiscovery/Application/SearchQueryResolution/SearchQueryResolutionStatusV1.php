<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolution;

enum SearchQueryResolutionStatusV1: string
{
    case Found = 'found';
    case Empty = 'empty';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
