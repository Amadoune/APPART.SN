<?php

namespace App\Application\ListingPublicationEventIntegration\Contract;

use Closure;

interface ListingPublicationAtomicTransaction
{
    public function run(Closure $operation): mixed;
}
