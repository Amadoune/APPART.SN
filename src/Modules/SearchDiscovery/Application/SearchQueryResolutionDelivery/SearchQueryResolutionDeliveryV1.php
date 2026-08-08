<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery;

final readonly class SearchQueryResolutionDeliveryV1
{
    public const TYPE = 'search_query_resolution.delivery.v1';

    public function __construct(public SearchQueryResolutionDeliveryPayload $payload) {}
}
