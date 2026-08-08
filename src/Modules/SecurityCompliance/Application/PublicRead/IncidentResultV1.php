<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead;

final readonly class IncidentResultV1
{
    public string $observedAt;

    public function __construct(public IncidentStatusV1 $status, SecurityComplianceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
