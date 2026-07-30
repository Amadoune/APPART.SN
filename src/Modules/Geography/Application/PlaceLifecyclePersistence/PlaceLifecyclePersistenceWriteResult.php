<?php

namespace Appart\Modules\Geography\Application\PlaceLifecyclePersistence;

enum PlaceLifecyclePersistenceWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case SourceVersionConflict = 'source_version_conflict';
    case TargetVersionConflict = 'target_version_conflict';
    case StateConflict = 'state_conflict';
    case TransitionRejected = 'transition_rejected';
}
