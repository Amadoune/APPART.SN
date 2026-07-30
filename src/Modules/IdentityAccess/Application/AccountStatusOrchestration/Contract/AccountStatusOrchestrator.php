<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\Contract;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationResult;

interface AccountStatusOrchestrator
{
    public function transition(AccountStatusContextV1 $context): AccountStatusOrchestrationResult;
}
