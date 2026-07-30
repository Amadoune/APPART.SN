<?php

namespace App\Application\AccountStatusEventConsumption;

enum AccountStatusConsumptionDiagnostic: string
{
    case UnsupportedMessage = 'unsupported_message';
    case CorruptedMessage = 'corrupted_message';
    case UnsupportedDestination = 'unsupported_destination';
}
