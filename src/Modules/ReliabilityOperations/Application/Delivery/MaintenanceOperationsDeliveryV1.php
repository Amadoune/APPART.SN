<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\MaintenanceOperationsEventType;

final readonly class MaintenanceOperationsDeliveryV1
{
    public function __construct(public MaintenanceOperationsEventType $type, public MaintenanceOperationsDeliveryPayload $payload) {}
}
