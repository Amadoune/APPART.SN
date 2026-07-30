<?php

namespace Tests\Unit\LeadEligibilitySource;

use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilitySourceDataReader;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceReadResult;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceRecord;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibility;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibilityEvidence;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\EligibilityRevision;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactability;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactEvidence;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;
use Appart\Modules\ContactsLeads\Infrastructure\EligibilitySource\MaterializedAdvertiserCatalog;
use Appart\Modules\ContactsLeads\Infrastructure\EligibilitySource\MaterializedListingCatalog;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class MaterializedEligibilityCatalogTest extends TestCase
{
    #[DataProvider('listingDecisions')]
    public function test_listing_decisions_are_copied_exactly(ListingContactability $decision): void
    {
        $record = $this->record(listing: $decision);
        $evidence = (new MaterializedListingCatalog(new EligibilityReaderStub(LeadEligibilitySourceReadResult::found($record))))->contactabilityOf($record->listingId);

        self::assertInstanceOf(ListingContactEvidence::class, $evidence);
        self::assertSame($decision, $evidence->state);
        self::assertSame($record->revision, $evidence->revision);
    }

    /** @return iterable<string, array{ListingContactability}> */
    public static function listingDecisions(): iterable
    {
        foreach (ListingContactability::cases() as $decision) {
            yield $decision->value => [$decision];
        }
    }

    #[DataProvider('advertiserDecisions')]
    public function test_advertiser_decisions_are_copied_exactly(AdvertiserEligibility $decision): void
    {
        $record = $this->record(advertiser: $decision);
        $evidence = (new MaterializedAdvertiserCatalog(new EligibilityReaderStub(LeadEligibilitySourceReadResult::found($record))))->eligibilityFor($record->evaluatedAdvertiserId, $record->listingId);

        self::assertInstanceOf(AdvertiserEligibilityEvidence::class, $evidence);
        self::assertSame($decision, $evidence->state);
        self::assertSame($record->revision, $evidence->revision);
    }

    /** @return iterable<string, array{AdvertiserEligibility}> */
    public static function advertiserDecisions(): iterable
    {
        foreach (AdvertiserEligibility::cases() as $decision) {
            yield $decision->value => [$decision];
        }
    }

    #[DataProvider('technicalReadFailures')]
    public function test_listing_technical_results_are_never_converted_to_domain_decisions(LeadEligibilitySourceReadResult $result): void
    {
        $this->expectException(RuntimeException::class);
        (new MaterializedListingCatalog(new EligibilityReaderStub($result)))->contactabilityOf($result->listingId);
    }

    #[DataProvider('technicalReadFailures')]
    public function test_advertiser_technical_results_are_never_converted_to_domain_decisions(LeadEligibilitySourceReadResult $result): void
    {
        $this->expectException(RuntimeException::class);
        (new MaterializedAdvertiserCatalog(new EligibilityReaderStub($result)))->eligibilityFor($this->advertiserId(), $result->listingId);
    }

    /** @return iterable<string, array{LeadEligibilitySourceReadResult}> */
    public static function technicalReadFailures(): iterable
    {
        $listing = ListingId::fromString('b5100000-0000-4000-8000-000000000001');
        yield 'source-absent' => [LeadEligibilitySourceReadResult::sourceAbsent($listing)];
        yield 'corrupted' => [LeadEligibilitySourceReadResult::corrupted($listing)];
    }

    public function test_advertiser_identity_mismatch_is_an_infrastructure_error_not_a_decision(): void
    {
        $record = $this->record();
        $this->expectException(RuntimeException::class);
        (new MaterializedAdvertiserCatalog(new EligibilityReaderStub(LeadEligibilitySourceReadResult::found($record))))->eligibilityFor(
            AdvertiserId::fromString('b5200000-0000-4000-8000-000000000099'),
            $record->listingId,
        );
    }

    private function record(
        ListingContactability $listing = ListingContactability::Contactable,
        AdvertiserEligibility $advertiser = AdvertiserEligibility::EligibleRecipient,
    ): LeadEligibilitySourceRecord {
        $revision = new EligibilityRevision('b5300000-0000-4000-8000-000000000001', 1, LeadTimestamp::at(new DateTimeImmutable('2026-07-23T10:00:00+00:00')));

        return new LeadEligibilitySourceRecord($this->listingId(), $this->advertiserId(), $listing, $this->advertiserId(), $advertiser, $revision);
    }

    private function listingId(): ListingId
    {
        return ListingId::fromString('b5100000-0000-4000-8000-000000000001');
    }

    private function advertiserId(): AdvertiserId
    {
        return AdvertiserId::fromString('b5200000-0000-4000-8000-000000000001');
    }
}

final readonly class EligibilityReaderStub implements LeadEligibilitySourceDataReader
{
    public function __construct(private LeadEligibilitySourceReadResult $result) {}

    public function current(ListingId $listingId): LeadEligibilitySourceReadResult
    {
        return $this->result;
    }

    public function history(ListingId $listingId): array
    {
        return [];
    }
}
