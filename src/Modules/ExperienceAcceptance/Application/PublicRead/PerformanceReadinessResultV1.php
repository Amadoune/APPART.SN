<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead;

final readonly class PerformanceReadinessResultV1
{
    public string $observedAt;

    public function __construct(public PerformanceReadinessStatusV1 $status, ExperienceAcceptanceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
