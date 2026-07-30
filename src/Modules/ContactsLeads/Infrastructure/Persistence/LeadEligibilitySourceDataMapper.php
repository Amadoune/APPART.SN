<?php

namespace Appart\Modules\ContactsLeads\Infrastructure\Persistence;

use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilityMaterialization;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceRecord;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibility;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\EligibilityRevision;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactability;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

final readonly class LeadEligibilitySourceDataMapper
{
    /** @return array{listing_id:string,version:int,normative_advertiser_id:?string,listing_decision:string,evaluated_advertiser_id:string,advertiser_decision:string,coherence_id:string,effective_at:string,checksum:string} */
    public function toRow(LeadEligibilityMaterialization $materialization): array
    {
        $revision = $materialization->listingRevision;
        $row = [
            'listing_id' => $materialization->listingId->value,
            'version' => $revision->version,
            'normative_advertiser_id' => $materialization->normativeAdvertiserId?->value,
            'listing_decision' => $materialization->listingDecision->value,
            'evaluated_advertiser_id' => $materialization->evaluatedAdvertiserId->value,
            'advertiser_decision' => $materialization->advertiserDecision->value,
            'coherence_id' => strtolower($revision->coherenceId),
            'effective_at' => $revision->effectiveAt->value->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z'),
        ];

        return $row + ['checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    public function toRecord(array $row): LeadEligibilitySourceRecord
    {
        try {
            if (! hash_equals((string) $row['materialization_checksum'], $this->checksum($row))) {
                throw new RuntimeException('Lead eligibility materialization checksum mismatch.');
            }

            return new LeadEligibilitySourceRecord(
                ListingId::fromString((string) $row['listing_id']),
                $row['normative_advertiser_id'] === null ? null : AdvertiserId::fromString((string) $row['normative_advertiser_id']),
                ListingContactability::from((string) $row['listing_decision']),
                AdvertiserId::fromString((string) $row['evaluated_advertiser_id']),
                AdvertiserEligibility::from((string) $row['advertiser_decision']),
                new EligibilityRevision(
                    (string) $row['coherence_id'],
                    (int) $row['version'],
                    LeadTimestamp::at(new DateTimeImmutable((string) $row['effective_at'])),
                ),
            );
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid lead eligibility source data row.', 0, $error);
        }
    }

    /** @param array<string, mixed> $row */
    public function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [
            strtolower((string) $row['listing_id']),
            (string) $row['version'],
            $row['normative_advertiser_id'] === null ? '' : strtolower((string) $row['normative_advertiser_id']),
            (string) $row['listing_decision'],
            strtolower((string) $row['evaluated_advertiser_id']),
            (string) $row['advertiser_decision'],
            strtolower((string) $row['coherence_id']),
            (new DateTimeImmutable((string) $row['effective_at']))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z'),
        ]));
    }
}
