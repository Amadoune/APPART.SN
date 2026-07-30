<?php

namespace Appart\Modules\IdentityAccess\Application\AccountAvailability;

enum AccountClosureReadStatus: string
{
    case Found = 'Found';
    case LegacyOpen = 'LegacyOpen';
    case PersistenceRejected = 'PersistenceRejected';
}
