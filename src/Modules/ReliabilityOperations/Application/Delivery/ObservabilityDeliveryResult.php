<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

final readonly class ObservabilityDeliveryResult
{
    public function __construct(public ObservabilityDeliveryV1 $delivery) {}

    public function status(): ObservabilityDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
