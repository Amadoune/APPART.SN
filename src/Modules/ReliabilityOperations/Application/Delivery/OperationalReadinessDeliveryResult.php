<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

final readonly class OperationalReadinessDeliveryResult
{
    public function __construct(public OperationalReadinessDeliveryV1 $delivery) {}

    public function status(): OperationalReadinessDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
