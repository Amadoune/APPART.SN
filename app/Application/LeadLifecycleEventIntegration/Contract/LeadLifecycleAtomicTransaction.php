<?php

namespace App\Application\LeadLifecycleEventIntegration\Contract;

use Closure;

interface LeadLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed;
}
