<?php

namespace Tests\Unit\Contracts\LeadEligibilitySourceData;

use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilityDecisionMaterializer;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilitySourceDataReader;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilityMaterialization;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilityMaterializationResult;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceReadStatus;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibility;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\EligibilityRevision;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactability;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

abstract class LeadEligibilityDecisionMaterializerContract extends TestCase
{
    abstract protected function materializer(): LeadEligibilityDecisionMaterializer;

    abstract protected function reader(): LeadEligibilitySourceDataReader;

    public function test_source_absence_is_not_a_missing_business_decision(): void
    {
        $result = $this->reader()->current($this->listingId());
        self::assertSame(LeadEligibilitySourceReadStatus::SourceAbsent, $result->status);
        self::assertNull($result->record);
    }

    public function test_exact_round_trip_history_and_idempotence(): void
    {
        $lot = $this->lot();
        self::assertSame(LeadEligibilityMaterializationResult::Created, $this->materializer()->materialize($lot));
        self::assertSame(LeadEligibilityMaterializationResult::AlreadyMaterialized, $this->materializer()->materialize($lot));

        $record = $this->reader()->current($this->listingId())->record;
        self::assertNotNull($record);
        self::assertEquals($lot->listingId, $record->listingId);
        self::assertEquals($lot->normativeAdvertiserId, $record->normativeAdvertiserId);
        self::assertSame($lot->listingDecision, $record->listingDecision);
        self::assertEquals($lot->evaluatedAdvertiserId, $record->evaluatedAdvertiserId);
        self::assertSame($lot->advertiserDecision, $record->advertiserDecision);
        self::assertTrue($lot->listingRevision->equals($record->revision));
        self::assertCount(1, $this->reader()->history($this->listingId()));
    }

    #[DataProvider('negativeDecisions')]
    public function test_negative_decisions_are_explicitly_persisted(
        ListingContactability $listing,
        AdvertiserEligibility $advertiser,
        int $number,
    ): void {
        $lot = $this->lot($number, 1, $listing, $advertiser, null);
        self::assertSame(LeadEligibilityMaterializationResult::Created, $this->materializer()->materialize($lot));
        $record = $this->reader()->current($lot->listingId)->record;
        self::assertSame($listing, $record?->listingDecision);
        self::assertSame($advertiser, $record?->advertiserDecision);
        self::assertSame(1, $record?->revision->version);
    }

    /** @return iterable<string, array{ListingContactability,AdvertiserEligibility,int}> */
    public static function negativeDecisions(): iterable
    {
        yield 'missing' => [ListingContactability::Missing, AdvertiserEligibility::Missing, 10];
        yield 'not-published-suspended' => [ListingContactability::NotPublished, AdvertiserEligibility::Suspended, 11];
        yield 'closed-not-recipient' => [ListingContactability::Closed, AdvertiserEligibility::NotListingRecipient, 12];
    }

    public function test_continuity_stale_divergent_and_relation_results_are_closed(): void
    {
        $this->materializer()->materialize($this->lot());
        self::assertSame(LeadEligibilityMaterializationResult::ContinuityConflict, $this->materializer()->materialize($this->lot(1, 3)));
        self::assertSame(LeadEligibilityMaterializationResult::DivergentVersion, $this->materializer()->materialize($this->lot(1, 1, advertiser: AdvertiserEligibility::Suspended)));
        self::assertSame(LeadEligibilityMaterializationResult::RelationDivergence, $this->materializer()->materialize($this->lot(1, 1, normative: $this->advertiserId(99))));
        self::assertSame(LeadEligibilityMaterializationResult::Created, $this->materializer()->materialize($this->lot(1, 2)));
        self::assertSame(LeadEligibilityMaterializationResult::StaleVersion, $this->materializer()->materialize($this->lot()));
        self::assertCount(2, $this->reader()->history($this->listingId()));
    }

    public function test_revision_divergences_are_rejected_before_persistence(): void
    {
        $base = $this->lot();
        foreach ([
            $this->revision(2, 1),
            $this->revision(1, 2),
            $this->revision(1, 1, '2026-07-22T11:00:00+00:00'),
        ] as $advertiserRevision) {
            $lot = new LeadEligibilityMaterialization(
                $base->listingId,
                $base->normativeAdvertiserId,
                $base->listingDecision,
                $base->listingRevision,
                $base->evaluatedAdvertiserId,
                $base->advertiserDecision,
                $advertiserRevision,
            );
            self::assertSame(LeadEligibilityMaterializationResult::IncoherentRevision, $this->materializer()->materialize($lot));
        }
        self::assertSame(LeadEligibilitySourceReadStatus::SourceAbsent, $this->reader()->current($this->listingId())->status);
    }

    protected function lot(
        int $number = 1,
        int $version = 1,
        ListingContactability $listing = ListingContactability::Contactable,
        AdvertiserEligibility $advertiser = AdvertiserEligibility::EligibleRecipient,
        ?AdvertiserId $normative = null,
    ): LeadEligibilityMaterialization {
        $revision = $this->revision($number, $version, sprintf('2026-07-22T%02d:00:00+00:00', 9 + $version));
        $evaluated = $this->advertiserId($number);

        return new LeadEligibilityMaterialization(
            $this->listingId($number),
            $normative ?? $evaluated,
            $listing,
            $revision,
            $evaluated,
            $advertiser,
            $revision,
        );
    }

    protected function listingId(int $number = 1): ListingId
    {
        return ListingId::fromString(sprintf('b4100000-0000-4000-8000-%012d', $number));
    }

    protected function advertiserId(int $number): AdvertiserId
    {
        return AdvertiserId::fromString(sprintf('b4200000-0000-4000-8000-%012d', $number));
    }

    private function revision(int $number, int $version, string $at = '2026-07-22T10:00:00+00:00'): EligibilityRevision
    {
        return new EligibilityRevision(
            sprintf('b4300000-0000-4000-8000-%012d', ($number * 100) + $version),
            $version,
            LeadTimestamp::at(new DateTimeImmutable($at)),
        );
    }
}
