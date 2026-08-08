<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

final readonly class PerformanceReadinessDeliveryPayload
{
    public function __construct(public PerformanceReadinessDeliveryStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
