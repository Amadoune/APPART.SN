<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\ContinuityEventV1;

final readonly class ContinuityDeliveryFactory
{
    public function create(ContinuityEventV1 $event): ContinuityDeliveryResult
    {
        return new ContinuityDeliveryResult(new ContinuityDeliveryV1($event->type, new ContinuityDeliveryPayload(ContinuityDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
