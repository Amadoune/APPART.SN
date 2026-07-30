<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

final readonly class PropertyLifecycleDecision
{
    private function __construct(
        public PropertyLifecycleWorkflowResult $result,
        public ?PropertyLifecycleTransition $transition,
        public ?PropertyLifecycleDiagnostic $diagnostic,
    ) {}

    public static function allowed(PropertyLifecycleTransition $transition): self
    {
        return new self(PropertyLifecycleWorkflowResult::Allowed, $transition, null);
    }

    public static function denied(PropertyLifecycleDiagnostic $diagnostic): self
    {
        return new self(PropertyLifecycleWorkflowResult::Denied, null, $diagnostic);
    }
}
