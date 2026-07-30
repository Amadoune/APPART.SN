<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence;

use Closure;

interface PropertyTransaction
{
    public function run(Closure $operation): mixed;
}
