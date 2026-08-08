<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery;

final readonly class SearchQueryResolutionDeliveryResult
{
    public function __construct(public SearchQueryResolutionDeliveryV1 $delivery) {}

    public function status(): SearchQueryResolutionDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
