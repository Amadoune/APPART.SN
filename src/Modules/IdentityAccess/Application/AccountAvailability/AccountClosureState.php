<?php

namespace Appart\Modules\IdentityAccess\Application\AccountAvailability;

enum AccountClosureState: string
{
    case Open = 'Open';
    case ClosureRequested = 'ClosureRequested';
    case Closed = 'Closed';
    case Reopened = 'Reopened';
}
