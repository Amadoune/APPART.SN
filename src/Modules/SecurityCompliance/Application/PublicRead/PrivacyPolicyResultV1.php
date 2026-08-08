<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead;

final readonly class PrivacyPolicyResultV1
{
    public string $observedAt;

    public function __construct(public PrivacyPolicyStatusV1 $status, SecurityComplianceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
