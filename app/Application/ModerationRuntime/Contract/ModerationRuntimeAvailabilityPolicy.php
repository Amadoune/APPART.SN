<?php

namespace App\Application\ModerationRuntime\Contract;

interface ModerationRuntimeAvailabilityPolicy
{
    public function inspect(): ModerationRuntimeReport;
}
