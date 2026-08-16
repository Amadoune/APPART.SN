<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Geography;

use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\RealEstateCatalog\Application\Contract\GeographicPlaceCatalog;
use Appart\Modules\RealEstateCatalog\Domain\Policy\GeographicPlaceAddressabilityPolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceAddressability;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceStatus;

final readonly class GeographyBackedGeographicPlaceCatalog implements GeographicPlaceCatalog
{
    public function __construct(private PlaceRegistry $places, private GeographicPlaceAddressabilityPolicy $addressability) {}

    public function statusOf(GeographicPlaceId $placeId): GeographicPlaceStatus
    {
        $place = $this->places->find(PlaceId::fromString($placeId->value));

        if ($place === null) {
            return GeographicPlaceStatus::NotFound;
        }

        if ($place->mergedInto() !== null) {
            return GeographicPlaceStatus::Merged;
        }

        if (! $place->isEnabled()) {
            return GeographicPlaceStatus::Disabled;
        }

        return $this->addressability->evaluate($place->type()) === GeographicPlaceAddressability::Addressable
            ? GeographicPlaceStatus::Usable
            : GeographicPlaceStatus::NotAddressable;
    }
}
