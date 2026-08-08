<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead;

use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerReadResult;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerReadStatus;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\Contract\SearchOwnerSourceRuntimeReadPolicy;

final readonly class DeterministicSearchOwnerSourceRuntimeReadPolicy implements SearchOwnerSourceRuntimeReadPolicy
{
    public function reduce(SearchOwnerReadResult $sourceResult): SearchOwnerSourceRuntimeReadResult
    {
        return match ($sourceResult->status) {
            SearchOwnerReadStatus::Found => SearchOwnerSourceRuntimeReadResult::allowed(),
            SearchOwnerReadStatus::Missing => SearchOwnerSourceRuntimeReadResult::empty(),
            SearchOwnerReadStatus::Corrupted => SearchOwnerSourceRuntimeReadResult::corrupted(),
            SearchOwnerReadStatus::DependencyUnavailable => SearchOwnerSourceRuntimeReadResult::dependencyUnavailable(),
        };
    }
}
