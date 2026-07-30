<?php

namespace Appart\Modules\ContactsLeads\Domain\Event;

use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactChannel;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactSubject;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;

final readonly class LeadCreated extends AbstractLeadEvent
{
    public function __construct(LeadId $lead, ListingId $listing, AdvertiserId $advertiser, public ContactChannel $channel, public ContactSubject $subject, LeadTimestamp $at)
    {
        parent::__construct($lead, $listing, $advertiser, $at);
    }
}
