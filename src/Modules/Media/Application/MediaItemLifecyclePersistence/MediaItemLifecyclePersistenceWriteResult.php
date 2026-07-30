<?php

namespace Appart\Modules\Media\Application\MediaItemLifecyclePersistence;

enum MediaItemLifecyclePersistenceWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case RejectedVersion = 'rejected_version';
    case StateConflict = 'state_conflict';
    case TransitionRejected = 'transition_rejected';
}
