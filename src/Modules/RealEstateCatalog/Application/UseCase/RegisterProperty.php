<?php

namespace Appart\Modules\RealEstateCatalog\Application\UseCase;

use Appart\Modules\RealEstateCatalog\Application\Contract\GeographicPlaceCatalog;
use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\Exception\UnavailableGeographicPlace;
use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceStatus;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use DateTimeImmutable;

final readonly class RegisterProperty
{
    public function __construct(private PropertyRegistry $properties, private GeographicPlaceCatalog $places, private PropertyTypePolicy $policy) {}

    public function execute(PropertyId $id, PropertyReference $reference, PropertyType $type, ?SurfaceArea $surface, RoomCount $rooms, BathroomCount $bathrooms, ?ConstructionYear $year, ?Address $address, BusinessYear $businessYear, DateTimeImmutable $at): Property
    {
        if ($address !== null) {
            $status = $this->places->statusOf($address->placeId);
            if ($status !== GeographicPlaceStatus::Usable) {
                throw new UnavailableGeographicPlace($status);
            }
        }

        $property = Property::register($id, $reference, $type, $surface, $rooms, $bathrooms, $year, $address, $businessYear, $this->policy, $at);
        $this->properties->add($property);

        return $property;
    }
}
