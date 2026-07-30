<?php

namespace Appart\Modules\ContactsLeads\Domain\Event;

use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;

abstract readonly class AbstractLeadEvent implements LeadEvent
{
    private LeadEventMetadata $metadata;

    public function __construct(private LeadId $leadId, private ListingId $listingId, private AdvertiserId $advertiserId, private LeadTimestamp $occurredAt)
    {
        $this->metadata = new LeadEventMetadata;
    }

    public function leadId(): LeadId
    {
        return $this->leadId;
    }

    public function listingId(): ListingId
    {
        return $this->listingId;
    }

    public function advertiserId(): AdvertiserId
    {
        return $this->advertiserId;
    }

    public function occurredAt(): LeadTimestamp
    {
        return $this->occurredAt;
    }

    public function aggregateVersion(): int
    {
        return $this->metadata->aggregateVersion;
    }

    public function eventIndex(): int
    {
        return $this->metadata->eventIndex;
    }

    final public function stamp(int $version, int $index): void
    {
        $this->metadata->aggregateVersion = $version;
        $this->metadata->eventIndex = $index;
    }
}
