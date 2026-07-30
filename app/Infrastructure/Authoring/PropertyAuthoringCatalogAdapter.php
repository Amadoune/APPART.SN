<?php

namespace App\Infrastructure\Authoring;

use Appart\Modules\ListingLifecycle\Application\Contract\PropertyCatalog;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;

final readonly class PropertyAuthoringCatalogAdapter implements PropertyCatalog
{
    public function __construct(private PropertyAuthoringStore $properties) {}

    public function availabilityOf(PropertyId $id): PropertyAvailability
    {
        return $this->properties->read($id->value) === null
            ? PropertyAvailability::Unavailable
            : PropertyAvailability::Eligible;
    }
}
