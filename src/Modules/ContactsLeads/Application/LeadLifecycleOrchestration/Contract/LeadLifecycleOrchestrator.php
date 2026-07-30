<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\Contract;

use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleTransitionRequest;

interface LeadLifecycleOrchestrator
{
    public function execute(LeadLifecycleTransitionRequest $request): LeadLifecycleOrchestrationResult;
}
