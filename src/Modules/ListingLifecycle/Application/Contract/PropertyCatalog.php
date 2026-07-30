<?php

namespace Appart\Modules\ListingLifecycle\Application\Contract;

use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;

interface PropertyCatalog
{
    public function availabilityOf(PropertyId $id): PropertyAvailability;
}
