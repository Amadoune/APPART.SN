<?php

namespace App\Application\AccountStatusEventRouting;

enum AccountStatusRoutingDiagnostic: string
{
    case UnsupportedMessage = 'unsupported_message';
    case CorruptedMessage = 'corrupted_message';
}
