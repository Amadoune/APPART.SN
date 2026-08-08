<?php

namespace Appart\Modules\SecurityCompliance\Application\Runtime;

interface SecurityComplianceRuntimeV1
{
    public function availability(): SecurityComplianceRuntimeAvailability;

    public function diagnostics(): SecurityComplianceRuntimeDiagnostics;
}
