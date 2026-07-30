<?php

namespace App\Application\PlaceLifecycleEventIntegration\Contract;

use Closure;

interface PlaceLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed;
}
