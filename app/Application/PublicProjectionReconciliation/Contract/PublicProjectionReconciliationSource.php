<?php

namespace App\Application\PublicProjectionReconciliation\Contract;

use App\Application\PublicProjectionReconciliation\PublicProjectionReconciliationPage;

interface PublicProjectionReconciliationSource
{
    public function read(?string $checkpoint, int $limit): PublicProjectionReconciliationPage;
}
