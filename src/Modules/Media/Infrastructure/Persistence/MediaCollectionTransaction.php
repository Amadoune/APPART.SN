<?php

namespace Appart\Modules\Media\Infrastructure\Persistence;

use Closure;

interface MediaCollectionTransaction
{
    public function run(Closure $operation): mixed;
}
