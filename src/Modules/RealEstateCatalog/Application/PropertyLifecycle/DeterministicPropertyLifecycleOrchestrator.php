<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleOrchestrator;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleWorkflowStore;
use Throwable;

final readonly class DeterministicPropertyLifecycleOrchestrator implements PropertyLifecycleOrchestrator
{
    public function __construct(
        private PropertyLifecycleWorkflow $workflow,
        private PropertyLifecycleWorkflowStore $store,
    ) {}

    public function transition(PropertyLifecycleOrchestrationRequest $request): PropertyLifecycleOrchestrationResult
    {
        try {
            $current = $this->store->read($request->propertyId);
        } catch (Throwable) {
            return PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::InfrastructureFailure);
        }

        if ($current->status !== PropertyLifecyclePersistenceReadStatus::Found || $current->snapshot === null) {
            return match ($current->status) {
                PropertyLifecyclePersistenceReadStatus::Missing => PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::CurrentStateMissing),
                PropertyLifecyclePersistenceReadStatus::Corrupted => PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::StoredStateCorrupted),
                PropertyLifecyclePersistenceReadStatus::Found => PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::StoredStateCorrupted),
            };
        }
        if ($current->snapshot->version !== $request->expectedVersion) {
            return PropertyLifecycleOrchestrationResult::concurrencyConflict(PropertyLifecycleOrchestrationDiagnosticCode::VersionConflict);
        }

        $decision = $this->workflow->decide($current->snapshot->state, $request->action);
        if ($decision->result === PropertyLifecycleWorkflowResult::Denied) {
            return $decision->diagnostic === null
                ? PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::InfrastructureFailure)
                : PropertyLifecycleOrchestrationResult::denied($decision->diagnostic);
        }
        if ($decision->transition === null) {
            return PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::InfrastructureFailure);
        }

        try {
            $persisted = $this->store->append($request->propertyId, $decision->transition, $request->expectedVersion + 1);
        } catch (Throwable) {
            return PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::InfrastructureFailure);
        }

        return match ($persisted) {
            PropertyLifecyclePersistenceWriteResult::Applied => PropertyLifecycleOrchestrationResult::applied($decision->transition),
            PropertyLifecyclePersistenceWriteResult::AlreadyApplied => PropertyLifecycleOrchestrationResult::alreadyApplied($decision->transition),
            PropertyLifecyclePersistenceWriteResult::RejectedVersion => PropertyLifecycleOrchestrationResult::concurrencyConflict(PropertyLifecycleOrchestrationDiagnosticCode::VersionConflict),
            PropertyLifecyclePersistenceWriteResult::StateConflict => PropertyLifecycleOrchestrationResult::concurrencyConflict(PropertyLifecycleOrchestrationDiagnosticCode::StateConflict),
            PropertyLifecyclePersistenceWriteResult::TransitionRejected => PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::TransitionRejected),
        };
    }
}
