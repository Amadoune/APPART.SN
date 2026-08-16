<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Policy;

use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceAddressability;

final readonly class GeographicPlaceAddressabilityPolicy
{
    public function evaluate(PlaceType $type): GeographicPlaceAddressability
    {
        return match ($type) {
            PlaceType::Country, PlaceType::Region, PlaceType::Department => GeographicPlaceAddressability::NotAddressable,
            PlaceType::City, PlaceType::District, PlaceType::Neighborhood => GeographicPlaceAddressability::Addressable,
        };
    }
}
