<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;

interface SearchQueryResolutionOwnerReaderV1
{
    public function read(
        SearchQuery $query,
        SearchQueryResolutionObservedAt $observedAt,
    ): SearchQueryResolutionOwnerResult;
}
