<?php

namespace App\Application\PublicProjectionRetry;

enum PublicProjectionReplayDecision: string
{
    case Authorized = 'authorized';
    case DeniedMissingAuthorization = 'denied_missing_authorization';
    case DeniedTerminalIncompatibility = 'denied_terminal_incompatibility';
}
