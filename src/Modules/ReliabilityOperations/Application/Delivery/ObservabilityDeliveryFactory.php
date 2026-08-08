<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\ObservabilityEventV1;

final readonly class ObservabilityDeliveryFactory
{
    public function create(ObservabilityEventV1 $event): ObservabilityDeliveryResult
    {
        return new ObservabilityDeliveryResult(new ObservabilityDeliveryV1($event->type, new ObservabilityDeliveryPayload(ObservabilityDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
