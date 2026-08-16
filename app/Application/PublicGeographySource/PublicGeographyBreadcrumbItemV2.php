<?php

namespace App\Application\PublicGeographySource;

use InvalidArgumentException;

final readonly class PublicGeographyBreadcrumbItemV2
{
    public function __construct(
        public string $placeId,
        public string $type,
        public string $officialName,
        public ?string $parentPlaceId,
        public int $aggregateVersion,
    ) {
        if (trim($placeId) === '' || trim($type) === '' || trim($officialName) === '' || $aggregateVersion < 1) {
            throw new InvalidArgumentException('Invalid public Geography V2 breadcrumb item.');
        }
        if ($parentPlaceId !== null && trim($parentPlaceId) === '') {
            throw new InvalidArgumentException('Invalid public Geography V2 parent identity.');
        }
    }
}
