<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead;

final readonly class SecurityAuditResultV1
{
    public string $observedAt;

    public function __construct(public SecurityAuditStatusV1 $status, SecurityComplianceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
