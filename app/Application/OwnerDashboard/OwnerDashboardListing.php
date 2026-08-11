<?php

namespace App\Application\OwnerDashboard;

use DateTimeImmutable;

final readonly class OwnerDashboardListing
{
    public function __construct(
        public string $canonicalPath,
        public string $title,
        public ?string $imageUrl,
        public ?string $transaction,
        public string $propertyType,
        public ?string $city,
        public string $status,
        public string $visibility,
        public DateTimeImmutable $publishedAt,
        public DateTimeImmutable $expiresAt,
    ) {}
}
