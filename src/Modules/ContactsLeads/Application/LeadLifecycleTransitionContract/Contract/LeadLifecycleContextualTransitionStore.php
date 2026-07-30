<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract;

use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadLifecyclePersistenceReadResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualAppend;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualWriteResult;

interface LeadLifecycleContextualTransitionStore
{
    public function read(LeadId $leadId): LeadLifecyclePersistenceReadResult;

    public function append(LeadLifecycleContextualAppend $append): LeadLifecycleContextualWriteResult;
}
