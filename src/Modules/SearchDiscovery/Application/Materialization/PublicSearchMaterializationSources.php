<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\MediaSearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\PropertySearchState;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevision;

final readonly class PublicSearchMaterializationSources
{
    public function __construct(
        public ListingSearchState $listingState,
        public PropertySearchState $propertyState,
        public MediaSearchState $mediaState,
        public SourceRevision $listingRevision,
        public SourceRevision $propertyRevision,
        public SourceRevision $mediaRevision,
    ) {}
}
