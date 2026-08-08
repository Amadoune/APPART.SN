<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead;

final readonly class SecretInventoryResultV1
{
    public string $observedAt;

    public function __construct(public SecretInventoryStatusV1 $status, SecurityComplianceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
