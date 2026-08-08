<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

final readonly class PrivacyPolicyDeliveryResult
{
    public function __construct(public PrivacyPolicyDeliveryV1 $delivery) {}

    public function status(): PrivacyPolicyDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
