<?php

namespace Appart\Modules\ListingLifecycle\Application\Creation;

final readonly class ListingCreationIntent
{
    public function __construct(
        public string $intentId,
        public string $checksum,
        public string $listingId,
        public string $propertyId,
        public ?int $aggregateVersion = null,
    ) {}

    public function isApplied(): bool
    {
        return $this->aggregateVersion !== null;
    }
}
