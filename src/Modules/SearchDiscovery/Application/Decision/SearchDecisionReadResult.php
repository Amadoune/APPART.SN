<?php

namespace Appart\Modules\SearchDiscovery\Application\Decision;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

final readonly class SearchDecisionReadResult
{
    private function __construct(public ListingId $listingId, public SearchDecisionReadStatus $status, public ?SearchDecision $decision) {}

    public static function found(ListingId $listingId, SearchDecision $decision): self
    {
        return new self($listingId, SearchDecisionReadStatus::Found, $decision);
    }

    public static function missing(ListingId $listingId): self
    {
        return new self($listingId, SearchDecisionReadStatus::Missing, null);
    }

    public static function corrupted(ListingId $listingId): self
    {
        return new self($listingId, SearchDecisionReadStatus::Corrupted, null);
    }
}
