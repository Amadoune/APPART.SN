<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

enum PropertyLifecyclePersistenceWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case RejectedVersion = 'rejected_version';
    case StateConflict = 'state_conflict';
    case TransitionRejected = 'transition_rejected';
}
