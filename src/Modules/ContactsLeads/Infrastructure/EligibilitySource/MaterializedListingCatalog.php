<?php

namespace Appart\Modules\ContactsLeads\Infrastructure\EligibilitySource;

use Appart\Modules\ContactsLeads\Application\Contract\ListingCatalog;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilitySourceDataReader;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceReadStatus;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceRecord;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactEvidence;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;
use RuntimeException;

final readonly class MaterializedListingCatalog implements ListingCatalog
{
    public function __construct(private LeadEligibilitySourceDataReader $reader) {}

    public function contactabilityOf(ListingId $listing): ListingContactEvidence
    {
        $result = $this->reader->current($listing);

        return match ($result->status) {
            LeadEligibilitySourceReadStatus::Found => $this->evidence($result->record),
            LeadEligibilitySourceReadStatus::SourceAbsent => throw new RuntimeException('Listing eligibility source is absent.'),
            LeadEligibilitySourceReadStatus::Corrupted => throw new RuntimeException('Listing eligibility source is corrupted.'),
        };
    }

    private function evidence(?LeadEligibilitySourceRecord $record): ListingContactEvidence
    {
        if ($record === null) {
            throw new RuntimeException('Found listing eligibility source has no record.');
        }

        return new ListingContactEvidence($record->listingDecision, $record->revision);
    }
}
