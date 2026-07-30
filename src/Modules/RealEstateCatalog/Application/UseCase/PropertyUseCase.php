<?php

namespace Appart\Modules\RealEstateCatalog\Application\UseCase;

use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyNotFound;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;

abstract readonly class PropertyUseCase
{
    public function __construct(protected PropertyRegistry $properties) {}

    protected function property(PropertyId $id): Property
    {
        return $this->properties->find($id) ?? throw new PropertyNotFound;
    }

    protected function save(Property $property, int $version): Property
    {
        $this->properties->save($property, $version);

        return $property;
    }
}
