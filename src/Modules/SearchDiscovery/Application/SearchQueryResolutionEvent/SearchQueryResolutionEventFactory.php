<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionStatusV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionPublicReader\PublicSearchQueryResolutionReaderV1;

final readonly class SearchQueryResolutionEventFactory
{
    public function __construct(private PublicSearchQueryResolutionReaderV1 $reader) {}

    public function create(
        SearchQuery $query,
        SearchQueryResolutionObservedAt $observedAt,
    ): SearchQueryResolutionEventV1 {
        $result = $this->reader->read($query, $observedAt);

        $status = match ($result->status) {
            SearchQueryResolutionStatusV1::Found => SearchQueryResolutionEventStatus::Found,
            SearchQueryResolutionStatusV1::Empty => SearchQueryResolutionEventStatus::Empty,
            SearchQueryResolutionStatusV1::Corrupted => SearchQueryResolutionEventStatus::Corrupted,
            SearchQueryResolutionStatusV1::DependencyUnavailable => SearchQueryResolutionEventStatus::DependencyUnavailable,
        };

        return new SearchQueryResolutionEventV1(
            SearchQueryResolutionEventType::ResolutionObserved,
            new SearchQueryResolutionEventPayload($status, $observedAt->canonical()),
        );
    }
}
