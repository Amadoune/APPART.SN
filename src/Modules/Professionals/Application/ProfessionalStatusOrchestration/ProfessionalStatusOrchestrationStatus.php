<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration;

enum ProfessionalStatusOrchestrationStatus: string
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
