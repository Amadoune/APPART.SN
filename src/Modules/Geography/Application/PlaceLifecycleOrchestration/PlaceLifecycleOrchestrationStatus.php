<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycleOrchestration;

enum PlaceLifecycleOrchestrationStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case WorkflowRefused = 'workflow_refused';
    case InspectionMissing = 'inspection_missing';
    case InspectionCorrupted = 'inspection_corrupted';
    case ContextDivergence = 'context_divergence';
    case ReplayConflict = 'replay_conflict';
    case SourceVersionConflict = 'source_version_conflict';
    case TargetVersionConflict = 'target_version_conflict';
    case StateConflict = 'state_conflict';
    case TransitionRejected = 'transition_rejected';
}
