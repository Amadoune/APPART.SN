<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

final readonly class ServiceHealthDeliveryResult
{
    public function __construct(public ServiceHealthDeliveryV1 $delivery) {}

    public function status(): ServiceHealthDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
