<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

final readonly class SecurityAuditDeliveryResult
{
    public function __construct(public SecurityAuditDeliveryV1 $delivery) {}

    public function status(): SecurityAuditDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
