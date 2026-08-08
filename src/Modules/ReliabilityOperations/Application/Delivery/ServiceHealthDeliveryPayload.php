<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

final readonly class ServiceHealthDeliveryPayload
{
    public function __construct(public ServiceHealthDeliveryStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
