<?php

namespace Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract;

use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceReadResult;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilitySourceRecord;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;

interface LeadEligibilitySourceDataReader
{
    public function current(ListingId $listingId): LeadEligibilitySourceReadResult;

    /** @return list<LeadEligibilitySourceRecord> */
    public function history(ListingId $listingId): array;
}
