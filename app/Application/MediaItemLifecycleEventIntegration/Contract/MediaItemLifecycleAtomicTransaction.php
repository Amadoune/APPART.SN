<?php

namespace App\Application\MediaItemLifecycleEventIntegration\Contract;

use Closure;

interface MediaItemLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed;
}
