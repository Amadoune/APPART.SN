<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

enum ListingPublicationOrchestrationStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case Denied = 'denied';
    case ConcurrencyConflict = 'concurrency_conflict';
    case PersistenceFailure = 'persistence_failure';
}
