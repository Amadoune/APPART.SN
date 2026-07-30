<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\Contract;

use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicCommand;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicWorkResult;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessOrchestrationResult;

interface IdentityAccessAtomicTransaction
{
    /**
     * @param  callable(): IdentityAccessAtomicWorkResult  $work
     */
    public function execute(IdentityAccessAtomicCommand $command, callable $work): IdentityAccessOrchestrationResult;
}
