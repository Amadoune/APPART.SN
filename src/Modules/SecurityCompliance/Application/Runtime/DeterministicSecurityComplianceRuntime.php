<?php

namespace Appart\Modules\SecurityCompliance\Application\Runtime;

final readonly class DeterministicSecurityComplianceRuntime implements SecurityComplianceRuntimeV1
{
    private const RUNTIME_ID = 'security-compliance.owner-source';

    private const VERSION = 'security-compliance-runtime-v1';

    public function __construct(private SecurityComplianceRuntimeAvailabilityPolicy $availabilityPolicy) {}

    public function availability(): SecurityComplianceRuntimeAvailability
    {
        return $this->availabilityPolicy->inspect();
    }

    public function diagnostics(): SecurityComplianceRuntimeDiagnostics
    {
        return new SecurityComplianceRuntimeDiagnostics(self::RUNTIME_ID, self::VERSION, $this->availability());
    }
}
