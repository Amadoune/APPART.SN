<?php

namespace Appart\Modules\ContactsLeads\Domain\Event;

use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadRejectionReason;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;

final readonly class LeadRejected extends AbstractLeadEvent
{
    public function __construct(LeadId $lead, ListingId $listing, AdvertiserId $advertiser, public LeadRejectionReason $reason, LeadTimestamp $at)
    {
        parent::__construct($lead, $listing, $advertiser, $at);
    }
}
