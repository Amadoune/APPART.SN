<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycle;

enum LeadLifecycleDiagnostic: string
{
    case TransitionForbidden = 'transition_forbidden';
    case IncompatibleState = 'incompatible_state';
    case TerminalState = 'terminal_state';
    case UnknownAction = 'unknown_action';
}
