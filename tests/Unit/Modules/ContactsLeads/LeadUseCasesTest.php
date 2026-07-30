<?php

namespace Tests\Unit\Modules\ContactsLeads;

use Appart\Modules\ContactsLeads\Application\UseCase\CloseLead;
use Appart\Modules\ContactsLeads\Application\UseCase\CreateLead;
use Appart\Modules\ContactsLeads\Application\UseCase\DeliverLead;
use Appart\Modules\ContactsLeads\Application\UseCase\RejectLead;
use Appart\Modules\ContactsLeads\Domain\Exception\ConcurrentLeadModification;
use Appart\Modules\ContactsLeads\Domain\Exception\LeadViolation;
use Appart\Modules\ContactsLeads\Domain\Exception\ListingNotContactable;
use Appart\Modules\ContactsLeads\Domain\Exception\LogicalLeadDuplicate;
use Appart\Modules\ContactsLeads\Domain\Model\Lead;
use Appart\Modules\ContactsLeads\Domain\Policy\ContactChannelPolicy;
use Appart\Modules\ContactsLeads\Domain\Policy\LeadSignaturePolicy;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibility;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactChannel;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactMessage;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactSubject;
use Appart\Modules\ContactsLeads\Domain\ValueObject\EligibilityRevision;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadRejectionReason;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadStatus;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactability;
use DateTimeImmutable;
use Tests\Unit\Modules\ContactsLeads\Support\FakeAdvertiserCatalog;
use Tests\Unit\Modules\ContactsLeads\Support\FakeLeadRegistry;
use Tests\Unit\Modules\ContactsLeads\Support\FakeListingCatalog;

final class LeadUseCasesTest extends ContactsLeadsTestCase
{
    private FakeLeadRegistry $registry;

    private FakeListingCatalog $listings;

    private FakeAdvertiserCatalog $advertisers;

    private CreateLead $create;

    protected function setUp(): void
    {
        $this->registry = new FakeLeadRegistry;
        $this->listings = new FakeListingCatalog;
        $this->advertisers = new FakeAdvertiserCatalog;
        $this->create = new CreateLead($this->registry, $this->listings, $this->advertisers, new ContactChannelPolicy, new LeadSignaturePolicy);
        $this->listings->set($this->listingId(), ListingContactability::Contactable);
        $this->advertisers->set($this->advertiserId(), $this->listingId(), AdvertiserEligibility::EligibleRecipient);
    }

    public function test_creation_stores_detached_event_free_snapshot(): void
    {
        $returned = $this->createLead();
        self::assertNotEmpty($returned->releaseEvents());
        self::assertSame([], $this->registry->find($this->leadId())?->releaseEvents());
    }

    public function test_non_contactable_listing_is_refused_without_reservation(): void
    {
        $this->listings->set($this->listingId(), ListingContactability::NotPublished);
        try {
            $this->createLead();
            self::fail('Must fail.');
        } catch (ListingNotContactable) {
            self::assertNull($this->registry->find($this->leadId()));
        }
    }

    public function test_incoherent_external_evidence_is_refused(): void
    {
        $revision = new EligibilityRevision('90000000-0000-4000-8000-000000000002', 2, $this->at(0));
        $this->advertisers->set($this->advertiserId(), $this->listingId(), AdvertiserEligibility::EligibleRecipient, $revision);
        $this->expectException(LeadViolation::class);
        $this->createLead();
    }

    public function test_duplicate_is_refused_in_rolling_window_and_allowed_after_it(): void
    {
        $this->createLead(1, LeadTimestamp::at(new DateTimeImmutable('2026-07-17T10:14:59+00:00')));
        try {
            $this->createLead(2, LeadTimestamp::at(new DateTimeImmutable('2026-07-17T10:15:00+00:00')));
            self::fail('Duplicate must fail.');
        } catch (LogicalLeadDuplicate) {
            self::assertNull($this->registry->find($this->leadId(2)));
        }
        $this->createLead(3, LeadTimestamp::at(new DateTimeImmutable('2026-07-17T10:30:00+00:00')));
        self::assertNotNull($this->registry->find($this->leadId(3)));
    }

    public function test_concurrent_duplicate_claim_has_one_winner(): void
    {
        $this->createLead();
        $this->expectException(LogicalLeadDuplicate::class);
        $this->createLead(2);
    }

    public function test_failed_add_rolls_back_identity_and_claim(): void
    {
        $this->registry->failNextWrite();
        try {
            $this->createLead();
            self::fail('Write must fail.');
        } catch (ConcurrentLeadModification) {
            self::assertNull($this->registry->find($this->leadId()));
        }
        $this->createLead();
        self::assertNotNull($this->registry->find($this->leadId()));
    }

    public function test_save_failure_does_not_leak_mutation(): void
    {
        $this->createLead();
        $this->registry->failNextWrite();
        try {
            (new DeliverLead($this->registry))->execute($this->leadId(), $this->at(2));
            self::fail('Save must fail.');
        } catch (ConcurrentLeadModification) {
            self::assertSame(LeadStatus::Created, $this->registry->find($this->leadId())?->status());
        }
    }

    public function test_stale_concurrent_copy_is_rejected(): void
    {
        $this->createLead();
        $first = $this->registry->find($this->leadId());
        $stale = $this->registry->find($this->leadId());
        self::assertNotNull($first);
        self::assertNotNull($stale);
        $first->deliver($this->at(2));
        $this->registry->save($first, 1);
        $stale->deliver($this->at(2));
        $this->expectException(ConcurrentLeadModification::class);
        $this->registry->save($stale, 1);
    }

    public function test_both_official_paths_are_orchestrated(): void
    {
        $this->createLead();
        (new DeliverLead($this->registry))->execute($this->leadId(), $this->at(2));
        (new CloseLead($this->registry))->execute($this->leadId(), $this->at(3));
        $this->createLead(2, $this->at(17));
        (new RejectLead($this->registry))->execute($this->leadId(2), LeadRejectionReason::Spam, $this->at(18));
        (new CloseLead($this->registry))->execute($this->leadId(2), $this->at(19));
        self::assertSame(LeadStatus::Closed, $this->registry->find($this->leadId())?->status());
        self::assertSame(LeadStatus::Closed, $this->registry->find($this->leadId(2))?->status());
    }

    private function createLead(int $suffix = 1, ?LeadTimestamp $at = null): Lead
    {
        return $this->create->execute($this->leadId($suffix), $this->listingId(), $this->advertiserId(), $this->visitor(), ContactChannel::Email, ContactSubject::GeneralInquiry, ContactMessage::fromString('Je souhaite recevoir davantage d’informations sur ce bien.'), $this->consent(), $at ?? $this->at(1));
    }
}
