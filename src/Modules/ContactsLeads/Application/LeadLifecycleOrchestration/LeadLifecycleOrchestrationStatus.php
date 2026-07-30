<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration;

enum LeadLifecycleOrchestrationStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case Missing = 'missing';
    case VersionConflict = 'version_conflict';
    case Denied = 'denied';
    case StateConflict = 'state_conflict';
    case ContextDivergence = 'context_divergence';
    case PersistenceCorrupted = 'persistence_corrupted';
}
