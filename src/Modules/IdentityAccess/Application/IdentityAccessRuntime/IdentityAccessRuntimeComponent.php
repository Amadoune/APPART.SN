<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime;

enum IdentityAccessRuntimeComponent: string
{
    case AccountRegistry = 'account_registry';
    case AccountStatusReader = 'account_status_reader';
    case AccountClosureReader = 'account_closure_reader';
    case AccountAvailability = 'account_availability';
}
