<?php

namespace App\Infrastructure\MediaAttachment;

use Appart\Modules\Media\Application\Contract\PropertyCatalog;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId as CatalogPropertyId;

final readonly class RegistryMediaPropertyCatalog implements PropertyCatalog
{
    public function __construct(private PropertyRegistry $properties) {}

    public function exists(PropertyId $propertyId): bool
    {
        return $this->properties->find(CatalogPropertyId::fromString($propertyId->value)) !== null;
    }
}
