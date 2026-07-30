<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleDiagnostic;

final readonly class LeadLifecycleOrchestrationResult
{
    public function __construct(public LeadLifecycleOrchestrationStatus $status, public ?LeadLifecycleDiagnostic $diagnostic = null) {}
}
