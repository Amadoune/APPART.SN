<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;

final readonly class LeadLifecycleStoredState
{
    public function __construct(
        public LeadId $leadId,
        public LeadLifecycleState $state,
        public int $version,
    ) {}
}
