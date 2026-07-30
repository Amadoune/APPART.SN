<?php

namespace App\Application\PropertyLifecycleEventIntegration\Contract;

use Closure;

interface PropertyLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed;
}
