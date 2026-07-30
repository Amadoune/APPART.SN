<?php

namespace Appart\Modules\ContentSeo\Application\Snapshot;

use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

final readonly class ContentSeoSnapshotReadResult
{
    private function __construct(public ListingId $listingId, public ContentSeoSnapshotReadStatus $status, public ?ContentSeoSourceDecision $snapshot) {}

    public static function found(ListingId $listingId, ContentSeoSourceDecision $snapshot): self
    {
        return new self($listingId, ContentSeoSnapshotReadStatus::Found, $snapshot);
    }

    public static function missing(ListingId $listingId): self
    {
        return new self($listingId, ContentSeoSnapshotReadStatus::Missing, null);
    }

    public static function corrupted(ListingId $listingId): self
    {
        return new self($listingId, ContentSeoSnapshotReadStatus::Corrupted, null);
    }
}
