<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Throwable;

final readonly class DeterministicListingPublicationOrchestrator implements ListingPublicationOrchestrator
{
    public function __construct(
        private ListingPublicationWorkflow $workflow,
        private ListingPublicationWorkflowStore $store,
    ) {}

    public function transition(ListingPublicationOrchestrationRequest $request): ListingPublicationOrchestrationResult
    {
        try {
            $current = $this->store->read($request->listingId);
        } catch (Throwable) {
            return ListingPublicationOrchestrationResult::persistenceFailure(ListingPublicationOrchestrationDiagnosticCode::InfrastructureFailure);
        }

        if ($current->status !== ListingPublicationPersistenceReadStatus::Found || $current->snapshot === null) {
            return match ($current->status) {
                ListingPublicationPersistenceReadStatus::Missing => ListingPublicationOrchestrationResult::persistenceFailure(ListingPublicationOrchestrationDiagnosticCode::CurrentStateMissing),
                ListingPublicationPersistenceReadStatus::Corrupted => ListingPublicationOrchestrationResult::persistenceFailure(ListingPublicationOrchestrationDiagnosticCode::StoredStateCorrupted),
                ListingPublicationPersistenceReadStatus::Found => ListingPublicationOrchestrationResult::persistenceFailure(ListingPublicationOrchestrationDiagnosticCode::StoredStateCorrupted),
            };
        }
        if ($current->snapshot->version !== $request->expectedVersion) {
            return ListingPublicationOrchestrationResult::concurrencyConflict(ListingPublicationOrchestrationDiagnosticCode::VersionConflict);
        }

        $decision = $this->workflow->decide($current->snapshot->state, $request->action);
        if ($decision->status === ListingPublicationDecisionStatus::Denied) {
            return $decision->diagnostic === null
                ? ListingPublicationOrchestrationResult::persistenceFailure(ListingPublicationOrchestrationDiagnosticCode::InfrastructureFailure)
                : ListingPublicationOrchestrationResult::denied($decision->diagnostic);
        }
        if ($decision->transition === null) {
            return ListingPublicationOrchestrationResult::persistenceFailure(ListingPublicationOrchestrationDiagnosticCode::InfrastructureFailure);
        }

        try {
            $persisted = $this->store->append($request->listingId, $decision->transition, $request->expectedVersion + 1);
        } catch (Throwable) {
            return ListingPublicationOrchestrationResult::persistenceFailure(ListingPublicationOrchestrationDiagnosticCode::InfrastructureFailure);
        }

        return match ($persisted) {
            ListingPublicationPersistenceWriteResult::Applied => ListingPublicationOrchestrationResult::applied($decision->transition),
            ListingPublicationPersistenceWriteResult::AlreadyApplied => ListingPublicationOrchestrationResult::alreadyApplied($decision->transition),
            ListingPublicationPersistenceWriteResult::RejectedVersion => ListingPublicationOrchestrationResult::concurrencyConflict(ListingPublicationOrchestrationDiagnosticCode::VersionConflict),
            ListingPublicationPersistenceWriteResult::StateConflict => ListingPublicationOrchestrationResult::concurrencyConflict(ListingPublicationOrchestrationDiagnosticCode::StateConflict),
            ListingPublicationPersistenceWriteResult::TransitionRejected => ListingPublicationOrchestrationResult::persistenceFailure(ListingPublicationOrchestrationDiagnosticCode::TransitionRejected),
        };
    }
}
