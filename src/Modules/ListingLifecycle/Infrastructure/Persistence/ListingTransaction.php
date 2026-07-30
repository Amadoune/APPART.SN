<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence;

use Closure;

interface ListingTransaction
{
    public function run(Closure $operation): mixed;
}
