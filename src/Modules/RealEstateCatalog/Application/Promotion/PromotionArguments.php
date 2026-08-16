<?php

namespace Appart\Modules\RealEstateCatalog\Application\Promotion;

use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;

final readonly class PromotionArguments
{
    public function __construct(
        public PropertyId $propertyId,
        public PropertyReference $reference,
        public PropertyType $type,
        public ?SurfaceArea $surface,
        public RoomCount $rooms,
        public BathroomCount $bathrooms,
        public ?ConstructionYear $constructionYear,
        public Address $address,
        public BusinessYear $businessYear,
    ) {}
}
