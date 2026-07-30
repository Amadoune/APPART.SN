<?php

namespace App\Application\AdministrativeActionLifecycleEventIntegration\Contract;

use Closure;

interface AdministrativeActionLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed;
}
