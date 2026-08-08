<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

final readonly class MaintenanceOperationsEventV1
{
    public function __construct(public MaintenanceOperationsEventType $type, public MaintenanceOperationsEventPayload $payload) {}
}
