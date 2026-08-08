<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\ServiceHealthEventV1;

final readonly class ServiceHealthDeliveryFactory
{
    public function create(ServiceHealthEventV1 $event): ServiceHealthDeliveryResult
    {
        return new ServiceHealthDeliveryResult(new ServiceHealthDeliveryV1($event->type, new ServiceHealthDeliveryPayload(ServiceHealthDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
