<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead;

final readonly class ReleaseCandidateResultV1
{
    public string $observedAt;

    public function __construct(public ReleaseCandidateStatusV1 $status, ExperienceAcceptanceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
