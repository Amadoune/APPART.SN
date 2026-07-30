<?php

namespace Appart\Modules\RealEstateCatalog\Application\UseCase;

use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use DateTimeImmutable;

final readonly class ArchiveProperty extends PropertyUseCase
{
    public function execute(PropertyId $id, DateTimeImmutable $at): Property
    {
        $property = $this->property($id);
        $version = $property->version();
        $property->archive($at);

        return $this->save($property, $version);
    }
}
