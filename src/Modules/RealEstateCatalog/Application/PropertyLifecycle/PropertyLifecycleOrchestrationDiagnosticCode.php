<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

enum PropertyLifecycleOrchestrationDiagnosticCode: string
{
    case CurrentStateMissing = 'current_state_missing';
    case StoredStateCorrupted = 'stored_state_corrupted';
    case VersionConflict = 'version_conflict';
    case StateConflict = 'state_conflict';
    case TransitionRejected = 'transition_rejected';
    case InfrastructureFailure = 'infrastructure_failure';
}
