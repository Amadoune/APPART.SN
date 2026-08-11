<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract;

use Closure;

interface ListingPublicationGatewayTransaction
{
    public function run(Closure $operation): mixed;
}
