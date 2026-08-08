<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

final readonly class ContinuityDeliveryResult
{
    public function __construct(public ContinuityDeliveryV1 $delivery) {}

    public function status(): ContinuityDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
