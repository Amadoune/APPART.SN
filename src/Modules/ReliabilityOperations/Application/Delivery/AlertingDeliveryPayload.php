<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

final readonly class AlertingDeliveryPayload
{
    public function __construct(public AlertingDeliveryStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
