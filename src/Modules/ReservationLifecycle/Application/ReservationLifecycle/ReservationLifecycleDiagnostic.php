<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle;

final readonly class ReservationLifecycleDiagnostic
{
    private function __construct(public ReservationLifecycleDiagnosticCode $code) {}

    public static function transitionForbidden(): self
    {
        return new self(ReservationLifecycleDiagnosticCode::TransitionForbidden);
    }

    public static function incompatibleState(): self
    {
        return new self(ReservationLifecycleDiagnosticCode::IncompatibleState);
    }

    public static function terminalState(): self
    {
        return new self(ReservationLifecycleDiagnosticCode::TerminalState);
    }

    public static function unknownAction(): self
    {
        return new self(ReservationLifecycleDiagnosticCode::UnknownAction);
    }

    public static function workflowCorrupted(): self
    {
        return new self(ReservationLifecycleDiagnosticCode::WorkflowCorrupted);
    }
}
