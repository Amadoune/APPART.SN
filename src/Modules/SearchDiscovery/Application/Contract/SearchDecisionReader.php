<?php

namespace Appart\Modules\SearchDiscovery\Application\Contract;

use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionReadResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

interface SearchDecisionReader
{
    public function readByListing(ListingId $listingId): SearchDecisionReadResult;
}
