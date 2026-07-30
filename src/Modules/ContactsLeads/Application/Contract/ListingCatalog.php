<?php

namespace Appart\Modules\ContactsLeads\Application\Contract;

use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactEvidence;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;

interface ListingCatalog
{
    public function contactabilityOf(ListingId $listing): ListingContactEvidence;
}
