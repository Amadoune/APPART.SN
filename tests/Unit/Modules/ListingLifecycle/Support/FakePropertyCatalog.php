<?php

namespace Tests\Unit\Modules\ListingLifecycle\Support;

use Appart\Modules\ListingLifecycle\Application\Contract\PropertyCatalog;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;

final class FakePropertyCatalog implements PropertyCatalog
{
    /** @var array<string, PropertyAvailability> */
    private array $properties = [];

    public function set(PropertyId $id, PropertyAvailability $availability): void
    {
        $this->properties[$id->value] = $availability;
    }

    public function availabilityOf(PropertyId $id): PropertyAvailability
    {
        return $this->properties[$id->value] ?? PropertyAvailability::Missing;
    }
}
