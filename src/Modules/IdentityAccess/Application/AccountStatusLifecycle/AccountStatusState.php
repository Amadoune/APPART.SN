<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle;

enum AccountStatusState: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}
