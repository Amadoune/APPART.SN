<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\Contract;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionPersistenceTransactionMode;
use Closure;

interface AdministrativeActionLifecycleAtomicPersistenceTransaction
{
    public function mode(): AdministrativeActionPersistenceTransactionMode;

    public function run(Closure $operation): mixed;
}
