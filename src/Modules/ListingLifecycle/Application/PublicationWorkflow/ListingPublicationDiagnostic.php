<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

final readonly class ListingPublicationDiagnostic
{
    private function __construct(public ListingPublicationDiagnosticCode $code) {}

    public static function transitionForbidden(): self
    {
        return new self(ListingPublicationDiagnosticCode::TransitionForbidden);
    }

    public static function incompatibleState(): self
    {
        return new self(ListingPublicationDiagnosticCode::IncompatibleState);
    }

    public static function terminalState(): self
    {
        return new self(ListingPublicationDiagnosticCode::TerminalState);
    }

    public static function unknownAction(): self
    {
        return new self(ListingPublicationDiagnosticCode::UnknownAction);
    }

    public static function workflowCorrupted(): self
    {
        return new self(ListingPublicationDiagnosticCode::WorkflowCorrupted);
    }
}
