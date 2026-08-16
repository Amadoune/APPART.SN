<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization\Contract;

use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationSourceResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

interface PublicSearchMaterializationSourceReaderV1
{
    public function read(ListingId $listingId): PublicSearchMaterializationSourceResult;
}
