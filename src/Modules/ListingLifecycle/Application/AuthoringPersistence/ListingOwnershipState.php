<?php

namespace Appart\Modules\ListingLifecycle\Application\AuthoringPersistence;

final readonly class ListingOwnershipState
{
    /** @param array<string, list<string>> $delegations */
    public function __construct(
        public string $listingId,
        public string $propertyId,
        public string $ownerAccountId,
        public array $delegations,
        public int $version,
        public string $intentId,
        public string $intentChecksum,
    ) {}
}
