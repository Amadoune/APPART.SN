<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

final readonly class AlertingDeliveryResult
{
    public function __construct(public AlertingDeliveryV1 $delivery) {}

    public function status(): AlertingDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
