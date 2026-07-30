<?php

namespace Appart\Modules\RealEstateCatalog\Application\Contract;

use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;

interface PropertyRegistry
{
    /** Returns a detached Aggregate without persisted domain events. */
    public function find(PropertyId $id): ?Property;

    /** Atomically adds a unique PropertyId and PropertyReference. */
    public function add(Property $property): void;

    /** Conditionally saves a clean snapshot when the expected version matches. */
    public function save(Property $property, int $expectedVersion): void;
}
