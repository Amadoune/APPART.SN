<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

final readonly class MaintenanceOperationsDeliveryResult
{
    public function __construct(public MaintenanceOperationsDeliveryV1 $delivery) {}

    public function status(): MaintenanceOperationsDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
