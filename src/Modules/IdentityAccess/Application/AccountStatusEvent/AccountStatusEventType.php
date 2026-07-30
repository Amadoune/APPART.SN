<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusEvent;

enum AccountStatusEventType: string
{
    case Suspended = 'account.status.suspended';
    case Reactivated = 'account.status.reactivated';
}
