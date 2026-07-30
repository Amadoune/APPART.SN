<?php

namespace Appart\Modules\ContactsLeads\Domain\Event;

final class LeadEventMetadata
{
    public function __construct(public int $aggregateVersion = 0, public int $eventIndex = 1) {}
}
