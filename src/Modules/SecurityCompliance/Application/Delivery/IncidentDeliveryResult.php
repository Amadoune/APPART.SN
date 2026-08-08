<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

final readonly class IncidentDeliveryResult
{
    public function __construct(public IncidentDeliveryV1 $delivery) {}

    public function status(): IncidentDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
