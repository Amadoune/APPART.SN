<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycle;

final readonly class LeadLifecycleDecision
{
    private function __construct(
        public LeadLifecycleWorkflowResult $result,
        public ?LeadLifecycleTransition $transition,
        public ?LeadLifecycleDiagnostic $diagnostic,
    ) {}

    public static function allowed(LeadLifecycleTransition $transition): self
    {
        return new self(LeadLifecycleWorkflowResult::Allowed, $transition, null);
    }

    public static function denied(LeadLifecycleDiagnostic $diagnostic): self
    {
        return new self(LeadLifecycleWorkflowResult::Denied, null, $diagnostic);
    }
}
