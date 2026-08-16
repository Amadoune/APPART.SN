<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization\Contract;

use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

interface MaterializePublicSearchDecisionV1
{
    public function materialize(ListingId $listingId): PublicSearchMaterializationResult;
}
