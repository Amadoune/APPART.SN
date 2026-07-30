<?php

namespace Appart\Modules\Geography\Application\PlaceMergeContext;

enum PlaceMergeReplayOutcome: string
{
    case AlreadyApplied = 'already_applied';
    case ContextDivergence = 'context_divergence';
    case Conflict = 'conflict';
    case InspectionMissing = 'inspection_missing';
    case InspectionCorrupted = 'inspection_corrupted';
}
