<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionPublicReader;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader\SearchQueryResolutionOwnerResult;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader\SearchQueryResolutionOwnerStatus;

final readonly class PublicSearchQueryResolutionReaderPolicy
{
    public function reduce(SearchQueryResolutionOwnerResult $result): PublicSearchQueryResolutionReaderResult
    {
        return new PublicSearchQueryResolutionReaderResult(match ($result->status) {
            SearchQueryResolutionOwnerStatus::Found => PublicSearchQueryResolutionReaderStatus::Found,
            SearchQueryResolutionOwnerStatus::Empty => PublicSearchQueryResolutionReaderStatus::Empty,
            SearchQueryResolutionOwnerStatus::Corrupted => PublicSearchQueryResolutionReaderStatus::Corrupted,
            SearchQueryResolutionOwnerStatus::DependencyUnavailable => PublicSearchQueryResolutionReaderStatus::DependencyUnavailable,
        });
    }
}
