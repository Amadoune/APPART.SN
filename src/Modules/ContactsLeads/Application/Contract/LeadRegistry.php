<?php

namespace Appart\Modules\ContactsLeads\Application\Contract;

use Appart\Modules\ContactsLeads\Domain\Model\Lead;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadDeduplicationClaim;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadId;

interface LeadRegistry
{
    /** Returns a detached aggregate reconstructed without previously published events. */
    public function find(LeadId $id): ?Lead;

    /** Atomically reserves LeadId and the sliding-window deduplication claim; nothing is visible on failure. */
    public function add(Lead $lead, LeadDeduplicationClaim $claim): void;

    /** Saves a clean snapshot only when the stored version equals expectedVersion. */
    public function save(Lead $lead, int $expectedVersion): void;
}
