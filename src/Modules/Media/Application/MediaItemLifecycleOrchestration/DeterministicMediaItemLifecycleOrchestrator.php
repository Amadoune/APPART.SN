<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleOrchestration;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleWorkflow;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleWorkflowResult;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualTransitionStore;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualAppend;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualInspectionStatus;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualWriteResult;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleReplayOutcome;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleReplayPolicy;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\Contract\MediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecyclePersistenceReadStatus;
use RuntimeException;

final readonly class DeterministicMediaItemLifecycleOrchestrator implements MediaItemLifecycleOrchestrator
{
    public function __construct(
        private MediaItemLifecycleContextualTransitionStore $store,
        private MediaItemLifecycleContextualReplayInspector $inspector,
        private MediaItemLifecycleReplayPolicy $replayPolicy,
        private MediaItemLifecycleWorkflow $workflow,
    ) {}

    public function execute(MediaItemLifecycleTransitionRequest $request): MediaItemLifecycleOrchestrationResult
    {
        $read = $this->store->read($request->mediaId);
        if ($read->status === MediaItemLifecyclePersistenceReadStatus::Missing) {
            return new MediaItemLifecycleOrchestrationResult(MediaItemLifecycleOrchestrationStatus::Missing);
        }
        if ($read->status === MediaItemLifecyclePersistenceReadStatus::Corrupted || $read->snapshot === null) {
            return new MediaItemLifecycleOrchestrationResult(MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted);
        }

        $expectedVersion = $request->context->expectedVersion->value;
        if ($read->snapshot->version === $expectedVersion + 1) {
            return $this->replay($request);
        }
        if ($read->snapshot->version !== $expectedVersion) {
            return new MediaItemLifecycleOrchestrationResult(MediaItemLifecycleOrchestrationStatus::VersionConflict);
        }

        $decision = $this->workflow->decide($read->snapshot->state, $request->action);
        if ($decision->result === MediaItemLifecycleWorkflowResult::Denied) {
            return new MediaItemLifecycleOrchestrationResult(MediaItemLifecycleOrchestrationStatus::Denied, $decision->diagnostic);
        }
        if ($decision->transition === null) {
            throw new RuntimeException('Allowed media item lifecycle decision has no transition.');
        }

        $write = $this->store->append(new MediaItemLifecycleContextualAppend($request->mediaId, $decision->transition, $request->context));

        return new MediaItemLifecycleOrchestrationResult(match ($write) {
            MediaItemLifecycleContextualWriteResult::Applied => MediaItemLifecycleOrchestrationStatus::Applied,
            MediaItemLifecycleContextualWriteResult::AlreadyApplied => MediaItemLifecycleOrchestrationStatus::AlreadyApplied,
            MediaItemLifecycleContextualWriteResult::ContextDivergence => MediaItemLifecycleOrchestrationStatus::ContextDivergence,
            MediaItemLifecycleContextualWriteResult::VersionConflict => MediaItemLifecycleOrchestrationStatus::VersionConflict,
            MediaItemLifecycleContextualWriteResult::StateConflict,
            MediaItemLifecycleContextualWriteResult::TransitionRejected => MediaItemLifecycleOrchestrationStatus::StateConflict,
            MediaItemLifecycleContextualWriteResult::Corrupted => MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted,
        });
    }

    private function replay(MediaItemLifecycleTransitionRequest $request): MediaItemLifecycleOrchestrationResult
    {
        $inspection = $this->inspector->inspectLatest($request->mediaId);
        if ($inspection->status === MediaItemLifecycleContextualInspectionStatus::Corrupted) {
            return new MediaItemLifecycleOrchestrationResult(MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted);
        }
        if ($inspection->status === MediaItemLifecycleContextualInspectionStatus::Missing || $inspection->snapshot === null) {
            return new MediaItemLifecycleOrchestrationResult(MediaItemLifecycleOrchestrationStatus::VersionConflict);
        }

        return new MediaItemLifecycleOrchestrationResult(match ($this->replayPolicy->classify($request->action, $request->context, $inspection->snapshot)) {
            MediaItemLifecycleReplayOutcome::AlreadyApplied => MediaItemLifecycleOrchestrationStatus::AlreadyApplied,
            MediaItemLifecycleReplayOutcome::ContextDivergence => MediaItemLifecycleOrchestrationStatus::ContextDivergence,
            MediaItemLifecycleReplayOutcome::Conflict => MediaItemLifecycleOrchestrationStatus::StateConflict,
        });
    }
}
