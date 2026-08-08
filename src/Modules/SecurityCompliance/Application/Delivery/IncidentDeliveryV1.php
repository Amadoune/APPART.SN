<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

use Appart\Modules\SecurityCompliance\Application\Event\IncidentEventType;

final readonly class IncidentDeliveryV1
{
    public function __construct(public IncidentEventType $type, public IncidentDeliveryPayload $payload) {}
}
