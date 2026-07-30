<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract;

use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;

final readonly class LeadLifecycleContextualInspectionResult
{
    private function __construct(
        public LeadId $leadId,
        public LeadLifecycleContextualInspectionStatus $status,
        public ?LeadLifecycleContextualAppendInspection $snapshot,
    ) {}

    public static function found(LeadLifecycleContextualAppendInspection $snapshot): self
    {
        return new self($snapshot->leadId, LeadLifecycleContextualInspectionStatus::Found, $snapshot);
    }

    public static function missing(LeadId $leadId): self
    {
        return new self($leadId, LeadLifecycleContextualInspectionStatus::Missing, null);
    }

    public static function corrupted(LeadId $leadId): self
    {
        return new self($leadId, LeadLifecycleContextualInspectionStatus::Corrupted, null);
    }
}
