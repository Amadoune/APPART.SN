<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

final readonly class ListingPublicationOrchestrationResult
{
    private function __construct(
        public ListingPublicationOrchestrationStatus $status,
        public ?ListingPublicationTransition $transition,
        public ?ListingPublicationDiagnostic $workflowDiagnostic,
        public ?ListingPublicationOrchestrationDiagnosticCode $orchestrationDiagnostic,
    ) {}

    public static function applied(ListingPublicationTransition $transition): self
    {
        return new self(ListingPublicationOrchestrationStatus::Applied, $transition, null, null);
    }

    public static function alreadyApplied(ListingPublicationTransition $transition): self
    {
        return new self(ListingPublicationOrchestrationStatus::AlreadyApplied, $transition, null, null);
    }

    public static function denied(ListingPublicationDiagnostic $diagnostic): self
    {
        return new self(ListingPublicationOrchestrationStatus::Denied, null, $diagnostic, null);
    }

    public static function concurrencyConflict(ListingPublicationOrchestrationDiagnosticCode $diagnostic): self
    {
        return new self(ListingPublicationOrchestrationStatus::ConcurrencyConflict, null, null, $diagnostic);
    }

    public static function persistenceFailure(ListingPublicationOrchestrationDiagnosticCode $diagnostic): self
    {
        return new self(ListingPublicationOrchestrationStatus::PersistenceFailure, null, null, $diagnostic);
    }
}
