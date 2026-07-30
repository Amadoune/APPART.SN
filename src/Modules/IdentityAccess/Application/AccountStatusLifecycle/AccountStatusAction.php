<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle;

enum AccountStatusAction: string
{
    case Suspend = 'suspend';
    case Reactivate = 'reactivate';
}
