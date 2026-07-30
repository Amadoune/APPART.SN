<?php

namespace App\Projections;

use DateTimeImmutable;

final readonly class SeoListingProjection
{
    /**
     * @param  list<array{url:string, disposition:string, effectiveAt:DateTimeImmutable, replacedAt:?DateTimeImmutable, redirectTarget:?string}>  $canonicalHistory
     * @param  list<array{label:string, url:string}>  $breadcrumb
     * @param  array{type:string, facts:array<string, string>}|null  $structuredData
     */
    public function __construct(
        public string $listingId,
        public string $canonicalUrl,
        public array $canonicalHistory,
        public ?string $headline,
        public ?string $description,
        public string $indexability,
        public string $robots,
        public string $htmlRobotsDirective,
        public array $breadcrumb,
        public ?array $structuredData,
        public ?string $publicJsonLd,
        public ?string $publicMediaUrl,
        public ?DateTimeImmutable $publishedAt,
        public ?DateTimeImmutable $expiresAt,
        public DateTimeImmutable $decidedAt,
        public string $expiredListingTreatment,
    ) {}
}
