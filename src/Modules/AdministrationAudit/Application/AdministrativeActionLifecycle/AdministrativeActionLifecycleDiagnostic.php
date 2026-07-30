<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle;

enum AdministrativeActionLifecycleDiagnostic: string
{
    case UnknownAction = 'unknown_action';
    case TerminalState = 'terminal_state';
    case IncompatibleState = 'incompatible_state';
    case MissingReason = 'missing_reason';
    case ApprovalNotRequired = 'approval_not_required';
}
