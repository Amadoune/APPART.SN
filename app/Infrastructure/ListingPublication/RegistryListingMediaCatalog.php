<?php

namespace App\Infrastructure\ListingPublication;

use Appart\Modules\ListingLifecycle\Application\Contract\MediaCatalog;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PublicationMediaAvailability;
use Appart\Modules\Media\Application\Contract\MediaCollectionOwnershipLookup;
use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Application\Ownership\MediaOwnershipResolution;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId as OwnedMediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\PropertyId as MediaPropertyId;

final readonly class RegistryListingMediaCatalog implements MediaCatalog
{
    public function __construct(
        private MediaCollectionOwnershipLookup $ownership,
        private MediaCollectionRegistry $collections,
    ) {}

    public function publicationAvailabilityOf(MediaCollectionId $collectionId, PropertyId $propertyId): PublicationMediaAvailability
    {
        $ownership = $this->ownership->resolve(MediaPropertyId::fromString($propertyId->value));
        if ($ownership->resolution === MediaOwnershipResolution::Missing) {
            return PublicationMediaAvailability::Missing;
        }
        if ($ownership->resolution !== MediaOwnershipResolution::Found || $ownership->collectionId?->value !== $collectionId->value) {
            return PublicationMediaAvailability::PropertyMismatch;
        }
        $collection = $this->collections->find(OwnedMediaCollectionId::fromString($collectionId->value));
        if ($collection === null || $collection->propertyId()->value !== $propertyId->value) {
            return PublicationMediaAvailability::PropertyMismatch;
        }

        return $collection->primary() === null
            ? PublicationMediaAvailability::WithoutPrimaryMedia
            : PublicationMediaAvailability::Eligible;
    }
}
