<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\Contract;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionResultV1;

interface SearchQueryResolutionReaderV1
{
    public function read(
        SearchQuery $query,
        SearchQueryResolutionObservedAt $observedAt,
    ): SearchQueryResolutionResultV1;
}
