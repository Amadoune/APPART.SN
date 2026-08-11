<?php

namespace App\Infrastructure\MediaAttachment;

use Appart\Modules\Media\Application\Contract\PropertyCatalog;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\Contract\PropertyAuthoringStore;

final readonly class PropertyAuthoringMediaCatalogAdapter implements PropertyCatalog
{
    public function __construct(private PropertyAuthoringStore $authoring) {}

    public function exists(PropertyId $propertyId): bool
    {
        $state = $this->authoring->read($propertyId->value);

        return $state !== null
            && $state->propertyId === $propertyId->value
            && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', strtolower($state->ownerAccountId)) === 1;
    }
}
