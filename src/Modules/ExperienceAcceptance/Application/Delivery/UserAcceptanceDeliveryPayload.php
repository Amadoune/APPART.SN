<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

final readonly class UserAcceptanceDeliveryPayload
{
    public function __construct(public UserAcceptanceDeliveryStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
