<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOutbox;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery\SearchQueryResolutionDeliveryV1;

interface SearchQueryResolutionOutboxWriter
{
    public function append(SearchQueryResolutionDeliveryV1 $delivery): SearchQueryResolutionOutboxResult;
}
