<?php

namespace Appart\Modules\SearchDiscovery\Application\Contract;

use Appart\Modules\SearchDiscovery\Domain\Model\PropertyProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

interface PropertyCatalog
{
    public function projectionFor(ListingId $id): ?PropertyProjectionSource;
}
