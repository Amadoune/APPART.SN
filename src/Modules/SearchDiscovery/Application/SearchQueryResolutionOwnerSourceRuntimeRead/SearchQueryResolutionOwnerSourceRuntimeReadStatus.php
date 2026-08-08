<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead;

enum SearchQueryResolutionOwnerSourceRuntimeReadStatus: string
{
    case Found = 'found';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
