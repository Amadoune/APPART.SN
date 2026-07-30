<?php

namespace Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData;

use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;

final readonly class LeadEligibilitySourceReadResult
{
    private function __construct(
        public ListingId $listingId,
        public LeadEligibilitySourceReadStatus $status,
        public ?LeadEligibilitySourceRecord $record,
    ) {}

    public static function found(LeadEligibilitySourceRecord $record): self
    {
        return new self($record->listingId, LeadEligibilitySourceReadStatus::Found, $record);
    }

    public static function sourceAbsent(ListingId $listingId): self
    {
        return new self($listingId, LeadEligibilitySourceReadStatus::SourceAbsent, null);
    }

    public static function corrupted(ListingId $listingId): self
    {
        return new self($listingId, LeadEligibilitySourceReadStatus::Corrupted, null);
    }
}
