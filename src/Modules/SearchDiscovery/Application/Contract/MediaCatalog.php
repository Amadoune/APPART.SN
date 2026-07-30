<?php

namespace Appart\Modules\SearchDiscovery\Application\Contract;

use Appart\Modules\SearchDiscovery\Domain\Model\MediaProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

interface MediaCatalog
{
    public function projectionFor(ListingId $id): ?MediaProjectionSource;
}
