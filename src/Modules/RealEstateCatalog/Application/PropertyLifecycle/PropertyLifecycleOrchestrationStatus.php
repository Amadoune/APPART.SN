<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

enum PropertyLifecycleOrchestrationStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case Denied = 'denied';
    case ConcurrencyConflict = 'concurrency_conflict';
    case PersistenceFailure = 'persistence_failure';
}
