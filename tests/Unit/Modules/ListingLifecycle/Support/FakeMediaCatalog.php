<?php

namespace Tests\Unit\Modules\ListingLifecycle\Support;

use Appart\Modules\ListingLifecycle\Application\Contract\MediaCatalog;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PublicationMediaAvailability;

final class FakeMediaCatalog implements MediaCatalog
{
    /** @var array<string, PublicationMediaAvailability> */
    private array $collections = [];

    public int $reads = 0;

    public function set(MediaCollectionId $id, PublicationMediaAvailability $availability): void
    {
        $this->collections[$id->value] = $availability;
    }

    public function publicationAvailabilityOf(MediaCollectionId $collectionId, PropertyId $propertyId): PublicationMediaAvailability
    {
        $this->reads++;

        return $this->collections[$collectionId->value] ?? PublicationMediaAvailability::Missing;
    }
}
