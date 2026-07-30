<?php

namespace Appart\Modules\ContactsLeads\Application\Contract;

use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibilityEvidence;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;

interface AdvertiserCatalog
{
    public function eligibilityFor(AdvertiserId $advertiser, ListingId $listing): AdvertiserEligibilityEvidence;
}
