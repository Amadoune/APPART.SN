<?php

namespace App\Application\PublicSearchResults;

final readonly class PublicSearchListingSummary
{
    public function __construct(
        public string $canonicalPath,
        public string $listingId,
        public ?string $headline,
        public string $propertyType,
        public ?string $primaryImageUrl,
        public ?string $transaction = null,
        public ?string $city = null,
        public ?int $surfaceSquareMeters = null,
        public ?int $roomCount = null,
    ) {}
}
