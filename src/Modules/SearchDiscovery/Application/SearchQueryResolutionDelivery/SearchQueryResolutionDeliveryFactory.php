<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent\SearchQueryResolutionEventStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent\SearchQueryResolutionEventV1;

final readonly class SearchQueryResolutionDeliveryFactory
{
    public function create(SearchQueryResolutionEventV1 $event): SearchQueryResolutionDeliveryResult
    {
        $status = match ($event->payload->status) {
            SearchQueryResolutionEventStatus::Found => SearchQueryResolutionDeliveryStatus::Found,
            SearchQueryResolutionEventStatus::Empty => SearchQueryResolutionDeliveryStatus::Empty,
            SearchQueryResolutionEventStatus::Corrupted => SearchQueryResolutionDeliveryStatus::Corrupted,
            SearchQueryResolutionEventStatus::DependencyUnavailable => SearchQueryResolutionDeliveryStatus::DependencyUnavailable,
        };

        return new SearchQueryResolutionDeliveryResult(
            new SearchQueryResolutionDeliveryV1(
                new SearchQueryResolutionDeliveryPayload($status, $event->payload->observedAt),
            ),
        );
    }
}
