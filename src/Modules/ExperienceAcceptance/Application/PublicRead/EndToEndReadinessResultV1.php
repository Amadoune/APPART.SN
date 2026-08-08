<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead;

final readonly class EndToEndReadinessResultV1
{
    public string $observedAt;

    public function __construct(public EndToEndReadinessStatusV1 $status, ExperienceAcceptanceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
