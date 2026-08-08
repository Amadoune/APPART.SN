<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead;

final readonly class AccessibilityComplianceResultV1
{
    public string $observedAt;

    public function __construct(public AccessibilityComplianceStatusV1 $status, ExperienceAcceptanceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
