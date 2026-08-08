<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

final readonly class UserAcceptanceEventPayload
{
    public function __construct(public UserAcceptanceEventStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
