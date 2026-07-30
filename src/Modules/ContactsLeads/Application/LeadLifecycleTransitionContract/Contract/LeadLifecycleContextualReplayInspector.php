<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract;

use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualInspectionResult;

interface LeadLifecycleContextualReplayInspector
{
    public function inspectLatest(LeadId $leadId): LeadLifecycleContextualInspectionResult;
}
