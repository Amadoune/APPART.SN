<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead;

final readonly class CryptographyPolicyResultV1
{
    public string $observedAt;

    public function __construct(public CryptographyPolicyStatusV1 $status, SecurityComplianceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
