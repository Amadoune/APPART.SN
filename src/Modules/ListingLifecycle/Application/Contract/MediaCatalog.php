<?php

namespace Appart\Modules\ListingLifecycle\Application\Contract;

use Appart\Modules\ListingLifecycle\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PublicationMediaAvailability;

interface MediaCatalog
{
    public function publicationAvailabilityOf(MediaCollectionId $collectionId, PropertyId $propertyId): PublicationMediaAvailability;
}
