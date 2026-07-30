<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

enum ListingPublicationDiagnosticCode: string
{
    case TransitionForbidden = 'transition_forbidden';
    case IncompatibleState = 'incompatible_state';
    case TerminalState = 'terminal_state';
    case UnknownAction = 'unknown_action';
    case WorkflowCorrupted = 'workflow_corrupted';
}
