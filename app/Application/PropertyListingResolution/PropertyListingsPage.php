<?php

namespace App\Application\PropertyListingResolution;

use InvalidArgumentException;

final readonly class PropertyListingsPage
{
    /** @param list<string> $listingIds */
    public function __construct(
        public PropertyListingsPageStatus $status,
        public array $listingIds,
        public ?string $nextCheckpoint,
        public bool $completed,
        public PropertyListingsDiagnostic $diagnostic = PropertyListingsDiagnostic::None,
    ) {
        $hasListings = $listingIds !== [];
        if (($status === PropertyListingsPageStatus::Empty && ($hasListings || ! $completed || $nextCheckpoint !== null))
            || ($status === PropertyListingsPageStatus::Found && (! $hasListings || $completed || $nextCheckpoint === null))
            || ($status === PropertyListingsPageStatus::Completed && (! $hasListings || ! $completed || $nextCheckpoint !== null))
            || (in_array($status, [PropertyListingsPageStatus::InvalidIdentity, PropertyListingsPageStatus::Corrupted], true)
                && ($hasListings || ! $completed || $nextCheckpoint !== null || $diagnostic === PropertyListingsDiagnostic::None))
            || (in_array($status, [PropertyListingsPageStatus::Empty, PropertyListingsPageStatus::Found, PropertyListingsPageStatus::Completed], true)
                && $diagnostic !== PropertyListingsDiagnostic::None)) {
            throw new InvalidArgumentException('Inconsistent Property-to-Listings page.');
        }
    }
}
