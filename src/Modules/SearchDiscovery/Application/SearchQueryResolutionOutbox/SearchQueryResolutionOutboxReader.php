<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOutbox;

interface SearchQueryResolutionOutboxReader
{
    /** @return list<SearchQueryResolutionOutboxResult> */
    public function pending(int $limit): array;
}
