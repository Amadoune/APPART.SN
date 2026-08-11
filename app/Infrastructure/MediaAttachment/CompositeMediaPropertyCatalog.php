<?php

namespace App\Infrastructure\MediaAttachment;

use Appart\Modules\Media\Application\Contract\PropertyCatalog;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;

final readonly class CompositeMediaPropertyCatalog implements PropertyCatalog
{
    public function __construct(
        private PropertyAuthoringMediaCatalogAdapter $authoring,
        private RegistryMediaPropertyCatalog $historical,
    ) {}

    public function exists(PropertyId $propertyId): bool
    {
        return $this->authoring->exists($propertyId) || $this->historical->exists($propertyId);
    }
}
