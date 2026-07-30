<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;

final readonly class PropertyLifecycleStoredState
{
    public function __construct(
        public PropertyId $propertyId,
        public PropertyLifecycleState $state,
        public int $version,
    ) {}
}
