<?php

namespace Appart\Modules\SearchDiscovery\Domain\Model;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\MediaSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevision;

final readonly class MediaProjectionSource
{
    /** @param list<SearchFacet> $facets */
    public function __construct(
        public ListingId $listingId,
        public MediaSearchState $state,
        public array $facets,
        public SourceRevision $revision,
    ) {}
}
