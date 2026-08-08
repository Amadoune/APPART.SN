<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

final readonly class MaintenanceOperationsDeliveryPayload
{
    public function __construct(public MaintenanceOperationsDeliveryStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
