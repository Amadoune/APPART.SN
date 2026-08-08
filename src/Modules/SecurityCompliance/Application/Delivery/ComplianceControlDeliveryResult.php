<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

final readonly class ComplianceControlDeliveryResult
{
    public function __construct(public ComplianceControlDeliveryV1 $delivery) {}

    public function status(): ComplianceControlDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
