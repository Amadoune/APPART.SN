<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOutbox;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery\SearchQueryResolutionDeliveryStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery\SearchQueryResolutionDeliveryV1;

final readonly class SearchQueryResolutionOutboxPolicy
{
    public function prepare(SearchQueryResolutionDeliveryV1 $delivery): SearchQueryResolutionOutboxResult
    {
        $status = match ($delivery->payload->status) {
            SearchQueryResolutionDeliveryStatus::Found => SearchQueryResolutionOutboxStatus::Found,
            SearchQueryResolutionDeliveryStatus::Empty => SearchQueryResolutionOutboxStatus::Empty,
            SearchQueryResolutionDeliveryStatus::Corrupted => SearchQueryResolutionOutboxStatus::Corrupted,
            SearchQueryResolutionDeliveryStatus::DependencyUnavailable => SearchQueryResolutionOutboxStatus::DependencyUnavailable,
        };
        $canonical = json_encode($delivery->payload->canonical(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        return new SearchQueryResolutionOutboxResult(hash('sha256', $canonical), $status, $delivery->payload->observedAt, false);
    }
}
