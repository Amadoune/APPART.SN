<?php

namespace Appart\Modules\SearchDiscovery\Domain\Model;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\PropertySearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevision;

final readonly class PropertyProjectionSource
{
    /** @param list<SearchFacet> $facets */
    public function __construct(
        public ListingId $listingId,
        public PropertySearchState $state,
        public array $facets,
        public SourceRevision $revision,
    ) {}
}
