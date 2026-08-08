<?php

namespace Appart\Modules\SecurityCompliance\Application\Runtime;

final readonly class SecurityComplianceRuntimeDiagnostics
{
    public function __construct(
        public string $runtimeId,
        public string $version,
        public SecurityComplianceRuntimeAvailability $availability,
    ) {}
}
