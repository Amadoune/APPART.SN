<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;

final readonly class PropertyLifecyclePersistenceReadResult
{
    private function __construct(
        public PropertyId $propertyId,
        public PropertyLifecyclePersistenceReadStatus $status,
        public ?PropertyLifecycleStoredState $snapshot,
    ) {}

    public static function found(PropertyLifecycleStoredState $snapshot): self
    {
        return new self($snapshot->propertyId, PropertyLifecyclePersistenceReadStatus::Found, $snapshot);
    }

    public static function missing(PropertyId $propertyId): self
    {
        return new self($propertyId, PropertyLifecyclePersistenceReadStatus::Missing, null);
    }

    public static function corrupted(PropertyId $propertyId): self
    {
        return new self($propertyId, PropertyLifecyclePersistenceReadStatus::Corrupted, null);
    }
}
