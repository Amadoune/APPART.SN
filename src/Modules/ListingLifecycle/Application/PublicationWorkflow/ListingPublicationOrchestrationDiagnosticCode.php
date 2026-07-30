<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

enum ListingPublicationOrchestrationDiagnosticCode: string
{
    case CurrentStateMissing = 'current_state_missing';
    case StoredStateCorrupted = 'stored_state_corrupted';
    case VersionConflict = 'version_conflict';
    case StateConflict = 'state_conflict';
    case TransitionRejected = 'transition_rejected';
    case InfrastructureFailure = 'infrastructure_failure';
}
