<?php

namespace Appart\Modules\Media\Application\Contract;

use Appart\Modules\Media\Application\Ownership\MediaOwnershipResult;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;

interface MediaCollectionOwnershipLookup
{
    public function resolve(PropertyId $propertyId): MediaOwnershipResult;
}
