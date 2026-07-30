<?php

namespace Tests\Unit\Contracts\RealEstateCatalog;

use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;

interface PropertyRegistryHarness
{
    public function freshRegistry(): PropertyRegistry;

    public function minimalProperty(?PropertyId $id = null, ?PropertyReference $reference = null): Property;

    public function propertyWithHistory(?PropertyId $id = null, ?PropertyReference $reference = null): Property;

    public function archivedProperty(?PropertyId $id = null, ?PropertyReference $reference = null): Property;

    public function primaryId(): PropertyId;

    public function distinctId(): PropertyId;

    public function primaryReference(): PropertyReference;

    public function distinctReference(): PropertyReference;

    public function mutate(Property $property): void;

    public function failNextWrite(PropertyRegistry $registry): void;
}
