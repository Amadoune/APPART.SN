<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\Contract;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadLifecyclePersistenceReadResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadLifecyclePersistenceWriteResult;

interface LeadLifecycleWorkflowStore
{
    public function initialize(LeadId $leadId, LeadLifecycleState $state): LeadLifecyclePersistenceWriteResult;

    public function append(LeadId $leadId, LeadLifecycleTransition $transition, int $version): LeadLifecyclePersistenceWriteResult;

    public function read(LeadId $leadId): LeadLifecyclePersistenceReadResult;
}
