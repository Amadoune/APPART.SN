<?php

namespace App\Application\ReservationLifecycleEventIntegration\Contract;

use Closure;

interface ReservationLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed;
}
