<?php

namespace Appart\Modules\ListingLifecycle\Application\Creation;

final readonly class CreateListingDraftResultV1
{
    private function __construct(
        public CreateListingDraftStatusV1 $status,
        public ?string $listingId = null,
        public ?string $propertyId = null,
        public ?int $aggregateVersion = null,
        public ?string $diagnosticCode = null,
    ) {}

    public static function applied(string $listingId, string $propertyId, int $version): self
    {
        return new self(CreateListingDraftStatusV1::Applied, $listingId, $propertyId, $version);
    }

    public static function alreadyApplied(string $listingId, string $propertyId, int $version): self
    {
        return new self(CreateListingDraftStatusV1::AlreadyApplied, $listingId, $propertyId, $version);
    }

    public static function failed(CreateListingDraftStatusV1 $status, string $diagnosticCode): self
    {
        return new self($status, diagnosticCode: $diagnosticCode);
    }
}
