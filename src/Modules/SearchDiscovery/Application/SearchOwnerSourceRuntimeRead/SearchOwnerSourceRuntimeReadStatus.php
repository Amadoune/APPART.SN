<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead;

enum SearchOwnerSourceRuntimeReadStatus: string
{
    case Allowed = 'allowed';
    case Empty = 'empty';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
