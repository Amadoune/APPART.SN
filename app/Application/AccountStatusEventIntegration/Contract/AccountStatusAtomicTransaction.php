<?php

namespace App\Application\AccountStatusEventIntegration\Contract;

use Closure;

interface AccountStatusAtomicTransaction
{
    public function run(Closure $lifecycleAndOutboxWrites): mixed;
}
