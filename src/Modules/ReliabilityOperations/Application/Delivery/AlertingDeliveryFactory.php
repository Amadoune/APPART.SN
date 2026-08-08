<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\AlertingEventV1;

final readonly class AlertingDeliveryFactory
{
    public function create(AlertingEventV1 $event): AlertingDeliveryResult
    {
        return new AlertingDeliveryResult(new AlertingDeliveryV1($event->type, new AlertingDeliveryPayload(AlertingDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
