<?php

namespace Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData;

use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibility;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\EligibilityRevision;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactability;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;

final readonly class LeadEligibilityMaterialization
{
    public function __construct(
        public ListingId $listingId,
        public ?AdvertiserId $normativeAdvertiserId,
        public ListingContactability $listingDecision,
        public EligibilityRevision $listingRevision,
        public AdvertiserId $evaluatedAdvertiserId,
        public AdvertiserEligibility $advertiserDecision,
        public EligibilityRevision $advertiserRevision,
    ) {}
}
