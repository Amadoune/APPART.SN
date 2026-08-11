<?php

namespace App\Application\OwnerDashboard\Contract;

use App\Application\OwnerDashboard\OwnerDashboardReadResult;

interface OwnerDashboardReadSourceV1
{
    public function read(): OwnerDashboardReadResult;
}
