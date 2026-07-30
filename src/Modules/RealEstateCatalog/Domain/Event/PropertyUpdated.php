<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Event;

use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use DateTimeImmutable;

final readonly class PropertyUpdated extends AbstractPropertyEvent
{
    public function __construct(PropertyId $id, public PropertyType $type, public ?SurfaceArea $surface, public RoomCount $rooms, public BathroomCount $bathrooms, public ?ConstructionYear $constructionYear, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
