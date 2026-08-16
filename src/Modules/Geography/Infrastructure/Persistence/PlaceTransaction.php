<?php

namespace Appart\Modules\Geography\Infrastructure\Persistence;

use Closure;

interface PlaceTransaction
{
    public function run(Closure $operation): mixed;
}
