<?php

namespace Appart\Modules\ContactsLeads\Application\UseCase;

use Appart\Modules\ContactsLeads\Application\Contract\AdvertiserCatalog;
use Appart\Modules\ContactsLeads\Application\Contract\LeadRegistry;
use Appart\Modules\ContactsLeads\Application\Contract\ListingCatalog;
use Appart\Modules\ContactsLeads\Domain\Model\Lead;
use Appart\Modules\ContactsLeads\Domain\Policy\ContactChannelPolicy;
use Appart\Modules\ContactsLeads\Domain\Policy\LeadSignaturePolicy;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ConsentProof;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactChannel;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactMessage;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactSubject;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadDeduplicationClaim;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadEligibilityProof;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\VisitorIdentity;

final readonly class CreateLead
{
    public function __construct(private LeadRegistry $leads, private ListingCatalog $listings, private AdvertiserCatalog $advertisers, private ContactChannelPolicy $channels, private LeadSignaturePolicy $signatures) {}

    public function execute(LeadId $id, ListingId $listing, AdvertiserId $advertiser, VisitorIdentity $visitor, ContactChannel $channel, ContactSubject $subject, ContactMessage $message, ConsentProof $consent, LeadTimestamp $at): Lead
    {
        $eligibility = LeadEligibilityProof::fromEvidence(
            $this->listings->contactabilityOf($listing),
            $this->advertisers->eligibilityFor($advertiser, $listing),
        );
        $lead = Lead::create($id, $listing, $advertiser, $visitor, $channel, $subject, $message, $consent, $eligibility, $at, $this->channels);
        $this->leads->add($lead, new LeadDeduplicationClaim($this->signatures->create($listing, $visitor, $subject, $message), $at));

        return $lead;
    }
}
