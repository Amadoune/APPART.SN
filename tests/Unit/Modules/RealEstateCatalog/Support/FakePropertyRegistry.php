<?php

namespace Tests\Unit\Modules\RealEstateCatalog\Support;

use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\Exception\ConcurrentPropertyModification;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyIdConflict;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyReferenceConflict;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;

final class FakePropertyRegistry implements PropertyRegistry
{
    /** @var array<string, Property> */
    private array $properties = [];

    private bool $failNextSave = false;

    public function find(PropertyId $id): ?Property
    {
        return isset($this->properties[$id->value]) ? clone $this->properties[$id->value] : null;
    }

    public function add(Property $property): void
    {
        foreach ($this->properties as $stored) {
            if ($stored->id()->equals($property->id())) {
                throw new PropertyIdConflict;
            }
            if ($stored->reference()->equals($property->reference())) {
                throw new PropertyReferenceConflict;
            }
        }
        $this->properties[$property->id()->value] = $this->cleanSnapshot($property);
    }

    public function save(Property $property, int $expectedVersion): void
    {
        if ($this->failNextSave) {
            $this->failNextSave = false;
            throw new ConcurrentPropertyModification;
        }
        $stored = $this->properties[$property->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentPropertyModification;
        }
        $this->properties[$property->id()->value] = $this->cleanSnapshot($property);
    }

    public function failNextSave(): void
    {
        $this->failNextSave = true;
    }

    private function cleanSnapshot(Property $property): Property
    {
        $snapshot = clone $property;
        $snapshot->releaseEvents();

        return $snapshot;
    }
}
