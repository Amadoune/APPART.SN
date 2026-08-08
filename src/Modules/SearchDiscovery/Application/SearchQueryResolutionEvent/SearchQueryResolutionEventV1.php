<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionEvent;

final readonly class SearchQueryResolutionEventV1
{
    public function __construct(
        public SearchQueryResolutionEventType $type,
        public SearchQueryResolutionEventPayload $payload,
    ) {}
}
