<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime;

enum SearchOwnerSourceRuntimeAvailability: string
{
    case Available = 'available';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
