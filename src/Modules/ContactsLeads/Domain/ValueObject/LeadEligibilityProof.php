<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

use Appart\Modules\ContactsLeads\Domain\Exception\AdvertiserNotEligible;
use Appart\Modules\ContactsLeads\Domain\Exception\LeadViolation;
use Appart\Modules\ContactsLeads\Domain\Exception\ListingNotContactable;

final readonly class LeadEligibilityProof
{
    private function __construct(public EligibilityRevision $revision) {}

    public static function fromEvidence(ListingContactEvidence $listing, AdvertiserEligibilityEvidence $advertiser): self
    {
        if ($listing->state !== ListingContactability::Contactable) {
            throw new ListingNotContactable;
        }
        if ($advertiser->state !== AdvertiserEligibility::EligibleRecipient) {
            throw new AdvertiserNotEligible;
        }
        if (! $listing->revision->equals($advertiser->revision)) {
            throw new LeadViolation('Listing and advertiser eligibility evidence is not coherent.');
        }

        return new self($listing->revision);
    }
}
