<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle;

final readonly class ReservationLifecycleDecision
{
    private function __construct(
        public ReservationLifecycleWorkflowResult $result,
        public ?ReservationLifecycleTransition $transition,
        public ?ReservationLifecycleDiagnostic $diagnostic,
    ) {}

    public static function allowed(ReservationLifecycleTransition $transition): self
    {
        return new self(ReservationLifecycleWorkflowResult::Allowed, $transition, null);
    }

    public static function denied(ReservationLifecycleDiagnostic $diagnostic): self
    {
        return new self(ReservationLifecycleWorkflowResult::Denied, null, $diagnostic);
    }
}
