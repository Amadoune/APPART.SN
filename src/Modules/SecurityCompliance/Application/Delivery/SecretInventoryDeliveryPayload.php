<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

final readonly class SecretInventoryDeliveryPayload
{
    public function __construct(public SecretInventoryDeliveryStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
