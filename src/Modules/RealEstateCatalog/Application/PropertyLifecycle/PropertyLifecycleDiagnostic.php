<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

final readonly class PropertyLifecycleDiagnostic
{
    private function __construct(public PropertyLifecycleDiagnosticCode $code) {}

    public static function transitionForbidden(): self
    {
        return new self(PropertyLifecycleDiagnosticCode::TransitionForbidden);
    }

    public static function incompatibleState(): self
    {
        return new self(PropertyLifecycleDiagnosticCode::IncompatibleState);
    }

    public static function terminalState(): self
    {
        return new self(PropertyLifecycleDiagnosticCode::TerminalState);
    }

    public static function unknownAction(): self
    {
        return new self(PropertyLifecycleDiagnosticCode::UnknownAction);
    }

    public static function workflowCorrupted(): self
    {
        return new self(PropertyLifecycleDiagnosticCode::WorkflowCorrupted);
    }
}
