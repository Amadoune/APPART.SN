<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycle;

enum MediaItemLifecycleDiagnostic: string
{
    case TerminalState = 'terminal_state';
    case UnknownAction = 'unknown_action';
}
