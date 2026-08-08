<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent;

enum SearchQueryResolutionEventType: string
{
    case ResolutionObserved = 'search_query_resolution.observed.v1';
}
