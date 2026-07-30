<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Exception;

use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceStatus;

final class UnavailableGeographicPlace extends RealEstateCatalogException
{
    public function __construct(public readonly GeographicPlaceStatus $status)
    {
        parent::__construct("The geographic place is not usable: {$status->value}.");
    }
}
