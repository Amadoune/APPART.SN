<?php

namespace App\Application\ProfessionalProfileRuntime\Contract;

interface ProfessionalProfileRuntimeAvailabilityPolicy
{
    public function inspect(): ProfessionalProfileRuntimeReport;
}
