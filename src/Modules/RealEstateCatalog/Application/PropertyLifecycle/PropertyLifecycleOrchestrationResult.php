<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

final readonly class PropertyLifecycleOrchestrationResult
{
    private function __construct(
        public PropertyLifecycleOrchestrationStatus $status,
        public ?PropertyLifecycleTransition $transition,
        public ?PropertyLifecycleDiagnostic $workflowDiagnostic,
        public ?PropertyLifecycleOrchestrationDiagnosticCode $orchestrationDiagnostic,
    ) {}

    public static function applied(PropertyLifecycleTransition $transition): self
    {
        return new self(PropertyLifecycleOrchestrationStatus::Applied, $transition, null, null);
    }

    public static function alreadyApplied(PropertyLifecycleTransition $transition): self
    {
        return new self(PropertyLifecycleOrchestrationStatus::AlreadyApplied, $transition, null, null);
    }

    public static function denied(PropertyLifecycleDiagnostic $diagnostic): self
    {
        return new self(PropertyLifecycleOrchestrationStatus::Denied, null, $diagnostic, null);
    }

    public static function concurrencyConflict(PropertyLifecycleOrchestrationDiagnosticCode $diagnostic): self
    {
        return new self(PropertyLifecycleOrchestrationStatus::ConcurrencyConflict, null, null, $diagnostic);
    }

    public static function persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode $diagnostic): self
    {
        return new self(PropertyLifecycleOrchestrationStatus::PersistenceFailure, null, null, $diagnostic);
    }
}
