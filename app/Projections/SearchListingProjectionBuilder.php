<?php

namespace App\Projections;

use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\Model\ListingRevision;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyStatus;

final readonly class SearchListingProjectionBuilder
{
    public function build(Property $property, MediaCollection $media, Listing $listing, ?string $transactionKind = null): ?SearchListingProjection
    {
        $publication = $this->publicationRevision($listing);
        $primary = $media->primary();
        $expiration = $listing->expirationDate();

        if (
            $listing->status() !== ListingStatus::Published
            || $property->status() === PropertyStatus::Archived
            || $publication === null
            || $expiration === null
            || $primary === null
            || $listing->propertyId()->value !== $property->id()->value
            || $media->propertyId()->value !== $property->id()->value
        ) {
            return null;
        }

        return new SearchListingProjection(
            listingId: $listing->id()->value,
            propertyId: $property->id()->value,
            mediaCollectionId: $media->id()->value,
            listingStatus: $listing->status()->value,
            geographicPlaceId: $property->address()?->placeId->value,
            propertyType: $property->type()->value,
            surfaceSquareMeters: $property->surface()?->squareMeters,
            roomCount: $property->rooms()->value,
            primaryMediaId: $primary->id->value,
            publishedAt: $publication->occurredAt,
            expiresAt: $expiration->value,
            transactionKind: $transactionKind,
        );
    }

    private function publicationRevision(Listing $listing): ?ListingRevision
    {
        foreach (array_reverse($listing->revisions()) as $revision) {
            if ($revision->status === ListingStatus::Published) {
                return $revision;
            }
        }

        return null;
    }
}
