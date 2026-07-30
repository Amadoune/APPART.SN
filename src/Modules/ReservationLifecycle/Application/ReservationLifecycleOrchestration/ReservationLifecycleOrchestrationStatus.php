<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration;

enum ReservationLifecycleOrchestrationStatus: string
{
    case Applied = 'applied';
    case Missing = 'missing';
    case VersionConflict = 'version_conflict';
    case StateConflict = 'state_conflict';
    case AlreadyApplied = 'already_applied';
    case Denied = 'denied';
    case PersistenceCorrupted = 'persistence_corrupted';
}
