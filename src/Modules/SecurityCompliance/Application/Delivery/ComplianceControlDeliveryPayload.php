<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

final readonly class ComplianceControlDeliveryPayload
{
    public function __construct(public ComplianceControlDeliveryStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
