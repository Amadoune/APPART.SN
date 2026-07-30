<?php

namespace App\Projections;

use DateTimeImmutable;

final readonly class SearchListingProjection
{
    public function __construct(
        public string $listingId,
        public string $propertyId,
        public string $mediaCollectionId,
        public string $listingStatus,
        public ?string $geographicPlaceId,
        public string $propertyType,
        public ?int $surfaceSquareMeters,
        public int $roomCount,
        public string $primaryMediaId,
        public DateTimeImmutable $publishedAt,
        public DateTimeImmutable $expiresAt,
    ) {}
}
