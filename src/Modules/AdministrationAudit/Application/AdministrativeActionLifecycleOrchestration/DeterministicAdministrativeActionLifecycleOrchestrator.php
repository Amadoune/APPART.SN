<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleWorkflow;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleWorkflowResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\Contract\AdministrativeActionLifecycleOrchestrator;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\AdministrativeActionLifecyclePersistenceReadStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualAppend;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualInspectionStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionContextualWriteResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionReplayOutcome;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionReplayPolicy;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualReplayInspector;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualTransitionStore;
use RuntimeException;

final readonly class DeterministicAdministrativeActionLifecycleOrchestrator implements AdministrativeActionLifecycleOrchestrator
{
    public function __construct(
        private AdministrativeActionContextualTransitionStore $store,
        private AdministrativeActionContextualReplayInspector $inspector,
        private AdministrativeActionReplayPolicy $replayPolicy,
        private AdministrativeActionLifecycleWorkflow $workflow,
    ) {}

    public function execute(AdministrativeActionLifecycleTransitionRequest $request): AdministrativeActionLifecycleOrchestrationResult
    {
        $read = $this->store->read($request->actionId);
        if ($read->status === AdministrativeActionLifecyclePersistenceReadStatus::NotEnrolled) {
            return $this->result(AdministrativeActionLifecycleOrchestrationStatus::Missing);
        }
        if ($read->status === AdministrativeActionLifecyclePersistenceReadStatus::Corrupted || $read->snapshot === null) {
            return $this->result(AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted);
        }

        $expected = $request->context->expectedVersion->value;
        if ($read->snapshot->version === $expected + 1) {
            return $this->replay($request);
        }
        if ($read->snapshot->version !== $expected) {
            return $this->result(AdministrativeActionLifecycleOrchestrationStatus::VersionConflict);
        }

        $decision = $this->workflow->decide(
            $read->snapshot->state,
            $request->action,
            $request->context->decisionContext,
        );
        if ($decision->result === AdministrativeActionLifecycleWorkflowResult::Denied) {
            return new AdministrativeActionLifecycleOrchestrationResult(
                AdministrativeActionLifecycleOrchestrationStatus::Denied,
                $decision->diagnostic,
            );
        }
        if ($decision->transition === null) {
            throw new RuntimeException('Allowed Administrative Action Lifecycle decision has no transition.');
        }

        $write = $this->store->append(new AdministrativeActionContextualAppend(
            $request->actionId,
            $decision->transition,
            $request->context,
        ));

        return $this->result(match ($write) {
            AdministrativeActionContextualWriteResult::Applied => AdministrativeActionLifecycleOrchestrationStatus::Applied,
            AdministrativeActionContextualWriteResult::AlreadyApplied => AdministrativeActionLifecycleOrchestrationStatus::AlreadyApplied,
            AdministrativeActionContextualWriteResult::ContextDivergence => AdministrativeActionLifecycleOrchestrationStatus::ContextDivergence,
            AdministrativeActionContextualWriteResult::VersionConflict => AdministrativeActionLifecycleOrchestrationStatus::VersionConflict,
            AdministrativeActionContextualWriteResult::StateConflict => AdministrativeActionLifecycleOrchestrationStatus::StateConflict,
            AdministrativeActionContextualWriteResult::TransitionDivergence => AdministrativeActionLifecycleOrchestrationStatus::TransitionDivergence,
            AdministrativeActionContextualWriteResult::EnrollmentDivergence,
            AdministrativeActionContextualWriteResult::Corrupted,
            AdministrativeActionContextualWriteResult::PersistenceCorrupted => AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted,
        });
    }

    private function replay(AdministrativeActionLifecycleTransitionRequest $request): AdministrativeActionLifecycleOrchestrationResult
    {
        $inspection = $this->inspector->inspectLatest($request->actionId);
        if ($inspection->status === AdministrativeActionContextualInspectionStatus::Missing) {
            return $this->result(AdministrativeActionLifecycleOrchestrationStatus::Missing);
        }
        if ($inspection->status === AdministrativeActionContextualInspectionStatus::Corrupted || $inspection->snapshot === null) {
            return $this->result(AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted);
        }

        return $this->result(match ($this->replayPolicy->classify(
            $request->action,
            $request->context,
            $inspection->snapshot,
        )) {
            AdministrativeActionReplayOutcome::AlreadyApplied => AdministrativeActionLifecycleOrchestrationStatus::AlreadyApplied,
            AdministrativeActionReplayOutcome::VersionConflict => AdministrativeActionLifecycleOrchestrationStatus::VersionConflict,
            AdministrativeActionReplayOutcome::ContextDivergence => AdministrativeActionLifecycleOrchestrationStatus::ContextDivergence,
            AdministrativeActionReplayOutcome::TransitionDivergence => AdministrativeActionLifecycleOrchestrationStatus::TransitionDivergence,
        });
    }

    private function result(
        AdministrativeActionLifecycleOrchestrationStatus $status,
    ): AdministrativeActionLifecycleOrchestrationResult {
        return new AdministrativeActionLifecycleOrchestrationResult($status);
    }
}
