<?php

namespace Appart\Modules\SearchDiscovery\Application\Contract;

use Appart\Modules\SearchDiscovery\Domain\Model\ListingProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

interface ListingCatalog
{
    public function projectionFor(ListingId $id): ?ListingProjectionSource;
}
