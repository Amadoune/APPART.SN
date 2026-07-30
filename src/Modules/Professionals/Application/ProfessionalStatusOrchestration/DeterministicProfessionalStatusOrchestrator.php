<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusWorkflow;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusWorkflowResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\Contract\ProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusPersistenceReadStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualTransitionStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualAppend;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualInspectionStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualWriteResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusReplayOutcome;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusReplayPolicy;
use RuntimeException;

final readonly class DeterministicProfessionalStatusOrchestrator implements ProfessionalStatusOrchestrator
{
    public function __construct(
        private ProfessionalStatusContextualTransitionStore $store,
        private ProfessionalStatusContextualReplayInspector $inspector,
        private ProfessionalStatusReplayPolicy $replayPolicy,
        private ProfessionalStatusWorkflow $workflow,
    ) {}

    public function execute(ProfessionalStatusTransitionRequest $request): ProfessionalStatusOrchestrationResult
    {
        $read = $this->store->read($request->professionalId);
        if ($read->status === ProfessionalStatusPersistenceReadStatus::Missing) {
            return new ProfessionalStatusOrchestrationResult(ProfessionalStatusOrchestrationStatus::Missing);
        }
        if ($read->status === ProfessionalStatusPersistenceReadStatus::Corrupted || $read->snapshot === null) {
            return new ProfessionalStatusOrchestrationResult(ProfessionalStatusOrchestrationStatus::PersistenceCorrupted);
        }

        $expectedVersion = $request->context->expectedVersion->value;
        if ($read->snapshot->version === $expectedVersion + 1) {
            return $this->replay($request);
        }
        if ($read->snapshot->version !== $expectedVersion) {
            return new ProfessionalStatusOrchestrationResult(ProfessionalStatusOrchestrationStatus::VersionConflict);
        }

        $decision = $this->workflow->decide($read->snapshot->state, $request->action);
        if ($decision->result === ProfessionalStatusWorkflowResult::Denied) {
            return new ProfessionalStatusOrchestrationResult(ProfessionalStatusOrchestrationStatus::Denied, $decision->diagnostic);
        }
        if ($decision->transition === null) {
            throw new RuntimeException('Allowed professional status decision has no transition.');
        }

        $write = $this->store->append(new ProfessionalStatusContextualAppend($request->professionalId, $decision->transition, $request->context));

        return new ProfessionalStatusOrchestrationResult(match ($write) {
            ProfessionalStatusContextualWriteResult::Applied => ProfessionalStatusOrchestrationStatus::Applied,
            ProfessionalStatusContextualWriteResult::AlreadyApplied => ProfessionalStatusOrchestrationStatus::AlreadyApplied,
            ProfessionalStatusContextualWriteResult::ContextDivergence => ProfessionalStatusOrchestrationStatus::ContextDivergence,
            ProfessionalStatusContextualWriteResult::VersionConflict => ProfessionalStatusOrchestrationStatus::VersionConflict,
            ProfessionalStatusContextualWriteResult::StateConflict,
            ProfessionalStatusContextualWriteResult::TransitionRejected => ProfessionalStatusOrchestrationStatus::StateConflict,
            ProfessionalStatusContextualWriteResult::Corrupted => ProfessionalStatusOrchestrationStatus::PersistenceCorrupted,
        });
    }

    private function replay(ProfessionalStatusTransitionRequest $request): ProfessionalStatusOrchestrationResult
    {
        $inspection = $this->inspector->inspectLatest($request->professionalId);
        if ($inspection->status === ProfessionalStatusContextualInspectionStatus::Corrupted) {
            return new ProfessionalStatusOrchestrationResult(ProfessionalStatusOrchestrationStatus::PersistenceCorrupted);
        }
        if ($inspection->status === ProfessionalStatusContextualInspectionStatus::Missing || $inspection->snapshot === null) {
            return new ProfessionalStatusOrchestrationResult(ProfessionalStatusOrchestrationStatus::VersionConflict);
        }

        return new ProfessionalStatusOrchestrationResult(match ($this->replayPolicy->classify($request->action, $request->context, $inspection->snapshot)) {
            ProfessionalStatusReplayOutcome::AlreadyApplied => ProfessionalStatusOrchestrationStatus::AlreadyApplied,
            ProfessionalStatusReplayOutcome::ContextDivergence => ProfessionalStatusOrchestrationStatus::ContextDivergence,
            ProfessionalStatusReplayOutcome::Conflict => ProfessionalStatusOrchestrationStatus::StateConflict,
        });
    }
}
