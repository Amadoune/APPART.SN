<?php

namespace Appart\Modules\ContactsLeads\Domain\Event;

use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;

interface LeadEvent
{
    public function leadId(): LeadId;

    public function listingId(): ListingId;

    public function advertiserId(): AdvertiserId;

    public function occurredAt(): LeadTimestamp;

    public function aggregateVersion(): int;

    public function eventIndex(): int;
}
