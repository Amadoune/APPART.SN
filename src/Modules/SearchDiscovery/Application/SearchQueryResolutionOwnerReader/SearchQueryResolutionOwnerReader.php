<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\Contract\SearchQueryResolutionOwnerSource;
use Throwable;

final readonly class SearchQueryResolutionOwnerReader implements SearchQueryResolutionOwnerReaderV1
{
    public function __construct(
        private SearchQueryResolutionOwnerSource $source,
        private SearchQueryResolutionOwnerPolicy $policy,
    ) {}

    public function read(
        SearchQuery $query,
        SearchQueryResolutionObservedAt $observedAt,
    ): SearchQueryResolutionOwnerResult {
        try {
            $sourceResult = $this->source->read($query, $observedAt);
        } catch (Throwable) {
            return new SearchQueryResolutionOwnerResult(
                SearchQueryResolutionOwnerStatus::DependencyUnavailable,
            );
        }

        return new SearchQueryResolutionOwnerResult(
            $this->policy->reduce($sourceResult->status),
        );
    }
}
