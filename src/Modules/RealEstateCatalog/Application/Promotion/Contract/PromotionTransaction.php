<?php

namespace Appart\Modules\RealEstateCatalog\Application\Promotion\Contract;

use Closure;

interface PromotionTransaction
{
    public function run(string $commandId, string $propertyId, Closure $operation): mixed;
}
