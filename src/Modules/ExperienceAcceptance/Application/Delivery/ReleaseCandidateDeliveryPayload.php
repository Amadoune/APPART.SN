<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

final readonly class ReleaseCandidateDeliveryPayload
{
    public function __construct(public ReleaseCandidateDeliveryStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
