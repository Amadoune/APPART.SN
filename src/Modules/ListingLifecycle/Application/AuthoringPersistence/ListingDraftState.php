<?php

namespace Appart\Modules\ListingLifecycle\Application\AuthoringPersistence;

final readonly class ListingDraftState
{
    public function __construct(
        public string $listingId,
        public string $propertyId,
        public string $title,
        public string $description,
        public string $transactionKind,
        public ?int $priceMinor,
        public ?string $currency,
        public ?int $chargesMinor,
        public ?string $availabilityDate,
        public string $contactPreference,
        public int $version,
        public string $intentId,
        public string $intentChecksum,
    ) {}
}
