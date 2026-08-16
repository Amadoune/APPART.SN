<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization\Contract;

use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

interface CatchUpPublicSearchDecisionV1
{
    public function catchUp(ListingId $listingId): PublicSearchMaterializationResult;
}
