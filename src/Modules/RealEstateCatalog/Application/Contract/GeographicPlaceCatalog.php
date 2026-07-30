<?php

namespace Appart\Modules\RealEstateCatalog\Application\Contract;

use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceStatus;

interface GeographicPlaceCatalog
{
    /** Usable means existing, enabled, not merged, and addressable. */
    public function statusOf(GeographicPlaceId $placeId): GeographicPlaceStatus;
}
