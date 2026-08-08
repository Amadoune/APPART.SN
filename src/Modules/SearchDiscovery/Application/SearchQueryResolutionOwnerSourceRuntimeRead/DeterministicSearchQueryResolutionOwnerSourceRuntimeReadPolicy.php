<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\SearchQueryResolutionOwnerSourceRuntimeAvailability;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\Contract\SearchQueryResolutionOwnerSourceRuntimeReadPolicy;

final readonly class DeterministicSearchQueryResolutionOwnerSourceRuntimeReadPolicy implements SearchQueryResolutionOwnerSourceRuntimeReadPolicy
{
    public function reduce(SearchQueryResolutionOwnerSourceRuntimeAvailability $availability): SearchQueryResolutionOwnerSourceRuntimeReadResult
    {
        return match ($availability) {
            SearchQueryResolutionOwnerSourceRuntimeAvailability::Available => SearchQueryResolutionOwnerSourceRuntimeReadResult::found(),
            SearchQueryResolutionOwnerSourceRuntimeAvailability::Corrupted => SearchQueryResolutionOwnerSourceRuntimeReadResult::corrupted(),
            SearchQueryResolutionOwnerSourceRuntimeAvailability::DependencyUnavailable => SearchQueryResolutionOwnerSourceRuntimeReadResult::dependencyUnavailable(),
        };
    }
}
