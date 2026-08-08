<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\OperationalReadinessEventV1;

final readonly class OperationalReadinessDeliveryFactory
{
    public function create(OperationalReadinessEventV1 $event): OperationalReadinessDeliveryResult
    {
        return new OperationalReadinessDeliveryResult(new OperationalReadinessDeliveryV1($event->type, new OperationalReadinessDeliveryPayload(OperationalReadinessDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
