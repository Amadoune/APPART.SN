<?php

namespace App\ReadModels;

use DateTimeImmutable;

final readonly class PublicListingReadModel
{
    /**
     * @param  list<array{url:string, disposition:string, effectiveAt:DateTimeImmutable, replacedAt:?DateTimeImmutable, redirectTarget:?string}>  $canonicalHistory
     * @param  list<array{label:string, url:string}>  $breadcrumb
     * @param  array{type:string, facts:array<string, string>}|null  $structuredData
     */
    public function __construct(
        public string $listingId,
        public string $propertyId,
        public string $mediaCollectionId,
        public ?string $headline,
        public ?string $description,
        public string $propertyType,
        public ?int $surfaceSquareMeters,
        public int $roomCount,
        public ?string $geographicPlaceId,
        public string $primaryMediaId,
        public ?string $publicMediaUrl,
        public string $listingStatus,
        public DateTimeImmutable $publishedAt,
        public DateTimeImmutable $expiresAt,
        public string $canonicalUrl,
        public array $canonicalHistory,
        public string $indexability,
        public string $robots,
        public string $htmlRobotsDirective,
        public array $breadcrumb,
        public ?array $structuredData,
        public ?string $publicJsonLd,
        public DateTimeImmutable $decidedAt,
        public string $expiredListingTreatment,
        public ?string $transactionKind = null,
        public ?string $city = null,
    ) {}
}
