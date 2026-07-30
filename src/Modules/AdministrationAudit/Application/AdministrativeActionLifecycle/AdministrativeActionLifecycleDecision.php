<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle;

final readonly class AdministrativeActionLifecycleDecision
{
    private function __construct(
        public AdministrativeActionLifecycleWorkflowResult $result,
        public ?AdministrativeActionLifecycleTransition $transition,
        public ?AdministrativeActionLifecycleDiagnostic $diagnostic,
    ) {}

    public static function allowed(AdministrativeActionLifecycleTransition $transition): self
    {
        return new self(AdministrativeActionLifecycleWorkflowResult::Allowed, $transition, null);
    }

    public static function denied(AdministrativeActionLifecycleDiagnostic $diagnostic): self
    {
        return new self(AdministrativeActionLifecycleWorkflowResult::Denied, null, $diagnostic);
    }
}
