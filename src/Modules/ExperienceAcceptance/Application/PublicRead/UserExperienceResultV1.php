<?php

namespace Appart\Modules\ExperienceAcceptance\Application\PublicRead;

final readonly class UserExperienceResultV1
{
    public string $observedAt;

    public function __construct(public UserExperienceStatusV1 $status, ExperienceAcceptanceObservedAt $observedAt)
    {
        $this->observedAt = $observedAt->canonical();
    }
}
