<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence;

final readonly class LeadLifecyclePersistenceReadResult
{
    private function __construct(
        public LeadId $leadId,
        public LeadLifecyclePersistenceReadStatus $status,
        public ?LeadLifecycleStoredState $snapshot,
    ) {}

    public static function found(LeadLifecycleStoredState $snapshot): self
    {
        return new self($snapshot->leadId, LeadLifecyclePersistenceReadStatus::Found, $snapshot);
    }

    public static function missing(LeadId $leadId): self
    {
        return new self($leadId, LeadLifecyclePersistenceReadStatus::Missing, null);
    }

    public static function corrupted(LeadId $leadId): self
    {
        return new self($leadId, LeadLifecyclePersistenceReadStatus::Corrupted, null);
    }
}
