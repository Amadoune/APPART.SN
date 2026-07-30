<?php

namespace Appart\Modules\SearchDiscovery\Domain\Model;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchRank;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevision;

final readonly class ListingProjectionSource
{
    /** @param list<SearchFacet> $facets */
    public function __construct(
        public ListingId $listingId,
        public ListingSearchState $state,
        public SearchRank $rank,
        public array $facets,
        public SourceRevision $revision,
    ) {}
}
