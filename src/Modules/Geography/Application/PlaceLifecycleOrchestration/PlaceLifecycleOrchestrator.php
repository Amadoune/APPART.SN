<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycleOrchestration;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleDecision;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleWorkflow;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\Contract\PlaceLifecycleWorkflowStore;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\PlaceLifecyclePersistenceWriteResult;
use Appart\Modules\Geography\Application\PlaceMergeContext\Contract\PlaceMergeContextInspector;
use Appart\Modules\Geography\Application\PlaceMergeContext\Contract\PlaceMergeReplayClassifier;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextInspectionStatus;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeReplayOutcome;
use RuntimeException;

final readonly class PlaceLifecycleOrchestrator
{
    public function __construct(
        private PlaceMergeContextInspector $inspector,
        private PlaceMergeReplayClassifier $classifier,
        private PlaceLifecycleWorkflow $workflow,
        private PlaceLifecycleWorkflowStore $store,
    ) {}

    public function execute(PlaceLifecycleOrchestrationRequest $request): PlaceLifecycleOrchestrationResult
    {
        $inspection = $this->inspector->inspect($request->context->sourceId, $request->context->intentId);

        if ($inspection->status === PlaceMergeContextInspectionStatus::Corrupted) {
            return $this->result(PlaceLifecycleOrchestrationStatus::InspectionCorrupted, $request);
        }

        $replayCandidate = false;

        if ($inspection->status === PlaceMergeContextInspectionStatus::Found) {
            $classification = $this->classifier->classify($request->context, $inspection);

            if ($classification !== PlaceMergeReplayOutcome::AlreadyApplied) {
                return $this->classifiedResult($classification, $request);
            }

            $replayCandidate = true;
        }

        $workflowResult = $this->workflow->decide($request->current, $request->action, $request->context);

        if ($workflowResult->decision !== PlaceLifecycleDecision::Applied) {
            return new PlaceLifecycleOrchestrationResult(
                PlaceLifecycleOrchestrationStatus::WorkflowRefused,
                $workflowResult->state,
                $workflowResult->decision,
            );
        }

        if ($workflowResult->transition === null) {
            throw new RuntimeException('An applied Place Lifecycle decision must contain a transition.');
        }

        $write = $this->store->append($workflowResult->transition, $request->context);

        return new PlaceLifecycleOrchestrationResult(
            $this->writeStatus($write, $replayCandidate),
            $write === PlaceLifecyclePersistenceWriteResult::Applied
                || $write === PlaceLifecyclePersistenceWriteResult::AlreadyApplied
                    ? $workflowResult->state
                    : $request->current->state,
        );
    }

    private function classifiedResult(
        PlaceMergeReplayOutcome $classification,
        PlaceLifecycleOrchestrationRequest $request,
    ): PlaceLifecycleOrchestrationResult {
        return $this->result(match ($classification) {
            PlaceMergeReplayOutcome::ContextDivergence => PlaceLifecycleOrchestrationStatus::ContextDivergence,
            PlaceMergeReplayOutcome::Conflict => PlaceLifecycleOrchestrationStatus::ReplayConflict,
            PlaceMergeReplayOutcome::InspectionMissing => PlaceLifecycleOrchestrationStatus::InspectionMissing,
            PlaceMergeReplayOutcome::InspectionCorrupted => PlaceLifecycleOrchestrationStatus::InspectionCorrupted,
            PlaceMergeReplayOutcome::AlreadyApplied => throw new RuntimeException('Replay candidates must continue to persistence.'),
        }, $request);
    }

    private function writeStatus(
        PlaceLifecyclePersistenceWriteResult $write,
        bool $replayCandidate,
    ): PlaceLifecycleOrchestrationStatus {
        return match ($write) {
            PlaceLifecyclePersistenceWriteResult::Applied => PlaceLifecycleOrchestrationStatus::Applied,
            PlaceLifecyclePersistenceWriteResult::AlreadyApplied => PlaceLifecycleOrchestrationStatus::AlreadyApplied,
            PlaceLifecyclePersistenceWriteResult::SourceVersionConflict => $replayCandidate
                ? PlaceLifecycleOrchestrationStatus::ReplayConflict
                : PlaceLifecycleOrchestrationStatus::SourceVersionConflict,
            PlaceLifecyclePersistenceWriteResult::TargetVersionConflict => PlaceLifecycleOrchestrationStatus::TargetVersionConflict,
            PlaceLifecyclePersistenceWriteResult::StateConflict => PlaceLifecycleOrchestrationStatus::StateConflict,
            PlaceLifecyclePersistenceWriteResult::TransitionRejected => PlaceLifecycleOrchestrationStatus::TransitionRejected,
        };
    }

    private function result(
        PlaceLifecycleOrchestrationStatus $status,
        PlaceLifecycleOrchestrationRequest $request,
    ): PlaceLifecycleOrchestrationResult {
        return new PlaceLifecycleOrchestrationResult($status, $request->current->state);
    }
}
