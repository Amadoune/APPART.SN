<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerReader;

final readonly class SearchQueryResolutionOwnerResult
{
    public function __construct(public SearchQueryResolutionOwnerStatus $status) {}
}
