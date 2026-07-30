<?php

namespace Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract;

use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringPersistenceWriteResult;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;

interface PropertyAuthoringStore
{
    public function read(string $propertyId): ?PropertyAuthoringState;

    public function save(PropertyAuthoringState $state, int $expectedVersion): PropertyAuthoringPersistenceWriteResult;
}
