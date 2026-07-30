<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecyclePersistenceReadResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecyclePersistenceWriteResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;

interface PropertyLifecycleWorkflowStore
{
    public function initialize(PropertyId $propertyId, PropertyLifecycleState $state): PropertyLifecyclePersistenceWriteResult;

    public function append(PropertyId $propertyId, PropertyLifecycleTransition $transition, int $version): PropertyLifecyclePersistenceWriteResult;

    public function read(PropertyId $propertyId): PropertyLifecyclePersistenceReadResult;
}
