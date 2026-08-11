<?php

namespace App\Infrastructure\ListingPublication;

use Appart\Modules\ListingLifecycle\Application\Contract\PropertyCatalog;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId as CatalogPropertyId;

final readonly class RegistryListingPropertyCatalog implements PropertyCatalog
{
    public function __construct(private PropertyRegistry $properties) {}

    public function availabilityOf(PropertyId $id): PropertyAvailability
    {
        return $this->properties->find(CatalogPropertyId::fromString($id->value)) === null
            ? PropertyAvailability::Unavailable
            : PropertyAvailability::Eligible;
    }
}
