<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\Contract;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionReadResult;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionRevisionState;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionWriteResult;

interface SearchQueryResolutionOwnerSource
{
    public function append(SearchQueryResolutionRevisionState $revision): SearchQueryResolutionWriteResult;

    public function read(SearchQuery $query, SearchQueryResolutionObservedAt $observedAt): SearchQueryResolutionReadResult;

    /** @return list<SearchQueryResolutionRevisionState> */
    public function history(SearchQuery $query): array;
}
