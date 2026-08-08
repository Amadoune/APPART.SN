<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead;

final readonly class ResponsiveComplianceResultV1
{
    public string $observedAt;

    public function __construct(public ResponsiveComplianceStatusV1 $status, ExperienceAcceptanceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
