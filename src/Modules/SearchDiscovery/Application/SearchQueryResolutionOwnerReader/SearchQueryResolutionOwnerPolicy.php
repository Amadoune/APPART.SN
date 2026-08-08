<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionReadStatus;

final readonly class SearchQueryResolutionOwnerPolicy
{
    public function reduce(SearchQueryResolutionReadStatus $status): SearchQueryResolutionOwnerStatus
    {
        return match ($status) {
            SearchQueryResolutionReadStatus::Found => SearchQueryResolutionOwnerStatus::Found,
            SearchQueryResolutionReadStatus::Empty => SearchQueryResolutionOwnerStatus::Empty,
            SearchQueryResolutionReadStatus::Missing,
            SearchQueryResolutionReadStatus::Corrupted => SearchQueryResolutionOwnerStatus::Corrupted,
            SearchQueryResolutionReadStatus::DependencyUnavailable => SearchQueryResolutionOwnerStatus::DependencyUnavailable,
        };
    }
}
