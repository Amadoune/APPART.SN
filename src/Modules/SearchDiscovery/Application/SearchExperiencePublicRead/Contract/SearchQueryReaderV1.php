<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\Contract;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQueryResultV1;

interface SearchQueryReaderV1
{
    public function read(
        SearchQuery $query,
        SearchObservedAt $observedAt,
    ): SearchQueryResultV1;
}
