<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

final readonly class CapacityPlanningDeliveryPayload
{
    public function __construct(public CapacityPlanningDeliveryStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
