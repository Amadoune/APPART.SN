<?php

namespace App\Application\ProfessionalStatusEventIntegration\Contract;

use Closure;

interface ProfessionalStatusAtomicTransaction
{
    public function run(Closure $operation): mixed;
}
