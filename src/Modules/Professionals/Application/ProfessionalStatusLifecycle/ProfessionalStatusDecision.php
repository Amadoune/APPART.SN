<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle;

final readonly class ProfessionalStatusDecision
{
    private function __construct(
        public ProfessionalStatusWorkflowResult $result,
        public ?ProfessionalStatusTransition $transition,
        public ?ProfessionalStatusDiagnostic $diagnostic,
    ) {}

    public static function allowed(ProfessionalStatusTransition $transition): self
    {
        return new self(ProfessionalStatusWorkflowResult::Allowed, $transition, null);
    }

    public static function denied(ProfessionalStatusDiagnostic $diagnostic): self
    {
        return new self(ProfessionalStatusWorkflowResult::Denied, null, $diagnostic);
    }
}
