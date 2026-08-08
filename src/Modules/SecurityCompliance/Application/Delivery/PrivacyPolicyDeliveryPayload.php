<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

final readonly class PrivacyPolicyDeliveryPayload
{
    public function __construct(public PrivacyPolicyDeliveryStatus $status, public string $observedAt) {}

    /** @return array{status:string, observedAt:string} */
    public function canonical(): array
    {
        return ['status' => $this->status->value, 'observedAt' => $this->observedAt];
    }
}
