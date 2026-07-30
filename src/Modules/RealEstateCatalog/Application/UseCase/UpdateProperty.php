<?php

namespace Appart\Modules\RealEstateCatalog\Application\UseCase;

use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use DateTimeImmutable;

final readonly class UpdateProperty extends PropertyUseCase
{
    public function __construct(PropertyRegistry $properties, private PropertyTypePolicy $policy)
    {
        parent::__construct($properties);
    }

    public function execute(PropertyId $id, PropertyType $type, ?SurfaceArea $surface, RoomCount $rooms, BathroomCount $bathrooms, ?ConstructionYear $year, BusinessYear $businessYear, DateTimeImmutable $at): Property
    {
        $property = $this->property($id);
        $version = $property->version();
        $property->update($type, $surface, $rooms, $bathrooms, $year, $businessYear, $this->policy, $at);

        return $this->save($property, $version);
    }
}
