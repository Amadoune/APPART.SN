<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime;

enum SearchQueryResolutionOwnerSourceRuntimeAvailability: string
{
    case Available = 'available';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
