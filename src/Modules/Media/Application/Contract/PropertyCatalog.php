<?php

namespace Appart\Modules\Media\Application\Contract;

use Appart\Modules\Media\Domain\ValueObject\PropertyId;

interface PropertyCatalog
{
    /** Read-only existence check; eligibility and lifecycle remain owned by their source domains. */
    public function exists(PropertyId $propertyId): bool;
}
