<?php

namespace Appart\Modules\SecurityCompliance\Application\Runtime;

interface SecurityComplianceRuntimeAvailabilityPolicy
{
    public function inspect(): SecurityComplianceRuntimeAvailability;
}
