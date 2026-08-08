<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead;

final readonly class UserAcceptanceResultV1
{
    public string $observedAt;

    public function __construct(public UserAcceptanceStatusV1 $status, ExperienceAcceptanceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
