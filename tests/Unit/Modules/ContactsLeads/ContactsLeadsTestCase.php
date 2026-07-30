<?php

namespace Tests\Unit\Modules\ContactsLeads;

use Appart\Modules\ContactsLeads\Domain\Model\Lead;
use Appart\Modules\ContactsLeads\Domain\Policy\ContactChannelPolicy;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibility;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibilityEvidence;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ConsentProof;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ConsentPurpose;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactChannel;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactCoordinates;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactMessage;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactSubject;
use Appart\Modules\ContactsLeads\Domain\ValueObject\EligibilityRevision;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadEligibilityProof;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactability;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactEvidence;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\VisitorIdentity;
use Appart\Modules\ContactsLeads\Domain\ValueObject\VisitorIdentityId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

abstract class ContactsLeadsTestCase extends TestCase
{
    protected function lead(): Lead
    {
        return Lead::create($this->leadId(), $this->listingId(), $this->advertiserId(), $this->visitor(), ContactChannel::Email, ContactSubject::GeneralInquiry, ContactMessage::fromString('Je souhaite recevoir davantage d’informations sur ce bien.'), $this->consent(), $this->eligibility(), $this->at(1), new ContactChannelPolicy);
    }

    protected function leadId(int $suffix = 1): LeadId
    {
        return LeadId::fromString(sprintf('b0000000-0000-4000-8000-%012d', $suffix));
    }

    protected function listingId(int $suffix = 1): ListingId
    {
        return ListingId::fromString(sprintf('70000000-0000-4000-8000-%012d', $suffix));
    }

    protected function advertiserId(int $suffix = 1): AdvertiserId
    {
        return AdvertiserId::fromString(sprintf('60000000-0000-4000-8000-%012d', $suffix));
    }

    protected function visitor(): VisitorIdentity
    {
        return VisitorIdentity::anonymous(VisitorIdentityId::fromString('a0000000-0000-4000-8000-000000000001'), 'Awa Ndiaye', ContactCoordinates::create('awa@example.test', '+221771234567'));
    }

    protected function consent(): ConsentProof
    {
        return ConsentProof::granted(ConsentPurpose::ContactRequest, 'contact-v1', $this->at(0));
    }

    protected function eligibility(): LeadEligibilityProof
    {
        $revision = new EligibilityRevision('90000000-0000-4000-8000-000000000001', 1, $this->at(0));

        return LeadEligibilityProof::fromEvidence(new ListingContactEvidence(ListingContactability::Contactable, $revision), new AdvertiserEligibilityEvidence(AdvertiserEligibility::EligibleRecipient, $revision));
    }

    protected function at(int $minute): LeadTimestamp
    {
        return LeadTimestamp::at((new DateTimeImmutable('2026-07-17T10:00:00+00:00'))->modify("+{$minute} minutes"));
    }
}
