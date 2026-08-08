<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead;

enum SearchOwnerSourceRuntimeReadAvailability: string
{
    case Available = 'available';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
