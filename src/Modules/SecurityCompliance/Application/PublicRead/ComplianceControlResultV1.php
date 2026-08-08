<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead;

final readonly class ComplianceControlResultV1
{
    public string $observedAt;

    public function __construct(public ComplianceControlStatusV1 $status, SecurityComplianceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
