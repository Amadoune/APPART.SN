<?php

namespace Appart\Modules\IdentityAccess\Application\AccountAvailability;

enum AccountAvailabilityStatus: string
{
    case Available = 'Available';
    case UnavailableSuspended = 'UnavailableSuspended';
    case UnavailableClosed = 'UnavailableClosed';
    case AccountMissing = 'AccountMissing';
    case Inconsistent = 'Inconsistent';
    case Indeterminate = 'Indeterminate';
}
