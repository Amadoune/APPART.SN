<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionPublicReader;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionResultV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader\SearchQueryResolutionOwnerReader;

final readonly class PublicSearchQueryResolutionReader implements PublicSearchQueryResolutionReaderV1
{
    public function __construct(
        private SearchQueryResolutionOwnerReader $ownerReader,
        private PublicSearchQueryResolutionReaderPolicy $policy,
    ) {}

    public function read(
        SearchQuery $query,
        SearchQueryResolutionObservedAt $observedAt,
    ): SearchQueryResolutionResultV1 {
        return $this->policy
            ->reduce($this->ownerReader->read($query, $observedAt))
            ->toPublicResultV1();
    }
}
