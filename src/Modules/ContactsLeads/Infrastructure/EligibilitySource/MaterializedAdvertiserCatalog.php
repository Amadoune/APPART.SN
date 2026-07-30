<?php

namespace Appart\Modules\ContactsLeads\Infrastructure\EligibilitySource;

use Appart\Modules\ContactsLeads\Application\Contract\AdvertiserCatalog;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilitySourceDataReader;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceReadStatus;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceRecord;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibilityEvidence;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;
use RuntimeException;

final readonly class MaterializedAdvertiserCatalog implements AdvertiserCatalog
{
    public function __construct(private LeadEligibilitySourceDataReader $reader) {}

    public function eligibilityFor(AdvertiserId $advertiser, ListingId $listing): AdvertiserEligibilityEvidence
    {
        $result = $this->reader->current($listing);

        return match ($result->status) {
            LeadEligibilitySourceReadStatus::Found => $this->evidence($advertiser, $result->record),
            LeadEligibilitySourceReadStatus::SourceAbsent => throw new RuntimeException('Advertiser eligibility source is absent.'),
            LeadEligibilitySourceReadStatus::Corrupted => throw new RuntimeException('Advertiser eligibility source is corrupted.'),
        };
    }

    private function evidence(AdvertiserId $advertiser, ?LeadEligibilitySourceRecord $record): AdvertiserEligibilityEvidence
    {
        if ($record === null) {
            throw new RuntimeException('Found advertiser eligibility source has no record.');
        }
        if ($record->evaluatedAdvertiserId->value !== $advertiser->value) {
            throw new RuntimeException('Advertiser eligibility source does not cover the requested advertiser.');
        }

        return new AdvertiserEligibilityEvidence($record->advertiserDecision, $record->revision);
    }
}
