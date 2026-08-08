<?php

namespace Appart\Modules\AdministrationConsole\Application\Delivery;

final readonly class AdministrationAuditDeliveryResult
{
    public function __construct(public AdministrationAuditDeliveryV1 $delivery) {}

    public function status(): AdministrationAuditDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
