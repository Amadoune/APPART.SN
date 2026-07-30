<?php

namespace Appart\Modules\ListingLifecycle\Application\Creation\Contract;

use Closure;

interface ListingCreationTransaction
{
    public function run(Closure $operation): mixed;
}
