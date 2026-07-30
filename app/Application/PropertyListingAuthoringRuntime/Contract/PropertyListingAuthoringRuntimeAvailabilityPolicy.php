<?php

namespace App\Application\PropertyListingAuthoringRuntime\Contract;

interface PropertyListingAuthoringRuntimeAvailabilityPolicy
{
    public function inspect(): PropertyListingAuthoringRuntimeReport;
}
