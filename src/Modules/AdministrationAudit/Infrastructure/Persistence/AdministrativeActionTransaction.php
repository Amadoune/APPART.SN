<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence;

use Closure;

interface AdministrativeActionTransaction
{
    public function run(Closure $operation): mixed;
}
