<?php

namespace Tests\Unit\Modules\ContactsLeads;

use Appart\Modules\ContactsLeads\Domain\Event\LeadClosed;
use Appart\Modules\ContactsLeads\Domain\Event\LeadCreated;
use Appart\Modules\ContactsLeads\Domain\Event\LeadRejected;
use Appart\Modules\ContactsLeads\Domain\Exception\LeadViolation;
use Appart\Modules\ContactsLeads\Domain\Model\Lead;
use Appart\Modules\ContactsLeads\Domain\Policy\ContactChannelPolicy;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ConsentProof;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ConsentPurpose;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactChannel;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactCoordinates;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactMessage;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactSubject;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadRejectionReason;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadStatus;
use Appart\Modules\ContactsLeads\Domain\ValueObject\VisitorIdentity;
use Appart\Modules\ContactsLeads\Domain\ValueObject\VisitorIdentityId;

final class LeadTest extends ContactsLeadsTestCase
{
    public function test_creation_records_complete_non_sensitive_event_and_proofs(): void
    {
        $lead = $this->lead();
        $event = $lead->releaseEvents()[0];
        self::assertSame(LeadStatus::Created, $lead->status());
        self::assertInstanceOf(LeadCreated::class, $event);
        self::assertSame('contact-v1', $lead->consent()->textVersion);
        self::assertSame(1, $lead->eligibility()->revision->version);
        self::assertObjectNotHasProperty('message', $event);
        self::assertObjectNotHasProperty('visitor', $event);
    }

    public function test_creation_requires_granted_prior_consent(): void
    {
        $this->expectException(LeadViolation::class);
        Lead::create($this->leadId(), $this->listingId(), $this->advertiserId(), $this->visitor(), ContactChannel::Email, ContactSubject::GeneralInquiry, ContactMessage::fromString('Message suffisamment détaillé.'), ConsentProof::denied(ConsentPurpose::ContactRequest, 'contact-v1', $this->at(0)), $this->eligibility(), $this->at(1), new ContactChannelPolicy);
    }

    public function test_future_consent_is_refused(): void
    {
        $this->expectException(LeadViolation::class);
        Lead::create($this->leadId(), $this->listingId(), $this->advertiserId(), $this->visitor(), ContactChannel::Email, ContactSubject::GeneralInquiry, ContactMessage::fromString('Message suffisamment détaillé.'), ConsentProof::granted(ConsentPurpose::ContactRequest, 'contact-v1', $this->at(2)), $this->eligibility(), $this->at(1), new ContactChannelPolicy);
    }

    public function test_channel_must_exist_on_contact_coordinates(): void
    {
        $visitor = VisitorIdentity::anonymous(VisitorIdentityId::fromString('a0000000-0000-4000-8000-000000000002'), 'Awa Ndiaye', ContactCoordinates::create('awa@example.test', null));
        $this->expectException(LeadViolation::class);
        Lead::create($this->leadId(), $this->listingId(), $this->advertiserId(), $visitor, ContactChannel::WhatsApp, ContactSubject::GeneralInquiry, ContactMessage::fromString('Message suffisamment détaillé.'), $this->consent(), $this->eligibility(), $this->at(1), new ContactChannelPolicy);
    }

    public function test_created_cannot_close_without_mutation(): void
    {
        $lead = $this->lead();
        $lead->releaseEvents();
        try {
            $lead->close($this->at(2));
            self::fail('Transition must fail.');
        } catch (LeadViolation) {
            self::assertSame(LeadStatus::Created, $lead->status());
            self::assertSame(1, $lead->version());
            self::assertCount(1, $lead->history());
            self::assertSame([], $lead->releaseEvents());
        }
    }

    public function test_delivered_then_closed_is_terminal(): void
    {
        $lead = $this->lead();
        $lead->deliver($this->at(2));
        $lead->close($this->at(3));
        self::assertSame(LeadStatus::Closed, $lead->status());
        self::assertInstanceOf(LeadClosed::class, $lead->releaseEvents()[2]);
        $this->expectException(LeadViolation::class);
        $lead->deliver($this->at(4));
    }

    public function test_rejected_then_closed_is_allowed_with_governed_reason(): void
    {
        $lead = $this->lead();
        $lead->reject(LeadRejectionReason::Spam, $this->at(2));
        self::assertSame('spam', $lead->history()[1]->reason);
        self::assertInstanceOf(LeadRejected::class, $lead->releaseEvents()[1]);
        $lead->close($this->at(3));
        self::assertSame(LeadStatus::Closed, $lead->status());
    }

    public function test_backdated_transition_has_no_mutation_or_event(): void
    {
        $lead = $this->lead();
        $lead->releaseEvents();
        try {
            $lead->deliver($this->at(0));
            self::fail('Backdating must fail.');
        } catch (LeadViolation) {
            self::assertSame(1, $lead->version());
            self::assertSame([], $lead->releaseEvents());
        }
    }

    public function test_release_events_prevents_replay(): void
    {
        $lead = $this->lead();
        self::assertNotEmpty($lead->releaseEvents());
        self::assertSame([], $lead->releaseEvents());
    }

    public function test_no_event_exposes_personal_data(): void
    {
        $delivered = $this->lead();
        $delivered->deliver($this->at(2));
        $delivered->close($this->at(3));
        $rejected = $this->lead();
        $rejected->reject(LeadRejectionReason::InvalidContactDetails, $this->at(2));
        $rejected->close($this->at(3));

        $payload = serialize([...$delivered->releaseEvents(), ...$rejected->releaseEvents()]);
        self::assertStringNotContainsString('awa@example.test', mb_strtolower($payload));
        self::assertStringNotContainsString('+221771234567', $payload);
        self::assertStringNotContainsString('Awa Ndiaye', $payload);
        self::assertStringNotContainsString('davantage d’informations', $payload);
    }
}
