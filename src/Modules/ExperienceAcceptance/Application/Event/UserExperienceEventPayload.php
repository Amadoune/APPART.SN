<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

final readonly class UserExperienceEventPayload
{
    public function __construct(public UserExperienceEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
