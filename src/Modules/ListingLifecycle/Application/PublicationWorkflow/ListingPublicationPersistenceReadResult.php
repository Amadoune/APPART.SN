<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

final readonly class ListingPublicationPersistenceReadResult
{
    private function __construct(
        public ListingId $listingId,
        public ListingPublicationPersistenceReadStatus $status,
        public ?ListingPublicationStoredState $snapshot,
    ) {}

    public static function found(ListingPublicationStoredState $snapshot): self
    {
        return new self($snapshot->listingId, ListingPublicationPersistenceReadStatus::Found, $snapshot);
    }

    public static function missing(ListingId $listingId): self
    {
        return new self($listingId, ListingPublicationPersistenceReadStatus::Missing, null);
    }

    public static function corrupted(ListingId $listingId): self
    {
        return new self($listingId, ListingPublicationPersistenceReadStatus::Corrupted, null);
    }
}
