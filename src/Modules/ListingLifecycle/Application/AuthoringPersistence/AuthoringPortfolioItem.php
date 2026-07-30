<?php

namespace Appart\Modules\ListingLifecycle\Application\AuthoringPersistence;

final readonly class AuthoringPortfolioItem
{
    public function __construct(
        public string $accountId,
        public string $listingId,
        public string $propertyId,
        public string $relation,
        public int $draftVersion,
        public int $ownershipVersion,
        public string $completenessCode,
        public int $sourceCheckpoint,
    ) {}
}
