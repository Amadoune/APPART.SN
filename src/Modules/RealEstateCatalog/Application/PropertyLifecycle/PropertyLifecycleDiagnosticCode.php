<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

enum PropertyLifecycleDiagnosticCode: string
{
    case TransitionForbidden = 'transition_forbidden';
    case IncompatibleState = 'incompatible_state';
    case TerminalState = 'terminal_state';
    case UnknownAction = 'unknown_action';
    case WorkflowCorrupted = 'workflow_corrupted';
}
