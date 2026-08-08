<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\MaintenanceOperationsEventV1;

final readonly class MaintenanceOperationsDeliveryFactory
{
    public function create(MaintenanceOperationsEventV1 $event): MaintenanceOperationsDeliveryResult
    {
        return new MaintenanceOperationsDeliveryResult(new MaintenanceOperationsDeliveryV1($event->type, new MaintenanceOperationsDeliveryPayload(MaintenanceOperationsDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
