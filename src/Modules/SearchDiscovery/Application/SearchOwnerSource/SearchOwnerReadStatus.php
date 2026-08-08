<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSource;

enum SearchOwnerReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
