<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\Contract;

use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationResult;

interface AccountStatusOrchestrationTransaction
{
    /** @param callable(): AccountStatusOrchestrationResult $operation */
    public function run(callable $operation): AccountStatusOrchestrationResult;
}
