<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

use Appart\Modules\SecurityCompliance\Application\Event\IncidentEventV1;

final readonly class IncidentDeliveryFactory
{
    public function create(IncidentEventV1 $event): IncidentDeliveryResult
    {
        return new IncidentDeliveryResult(new IncidentDeliveryV1($event->type, new IncidentDeliveryPayload(IncidentDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
