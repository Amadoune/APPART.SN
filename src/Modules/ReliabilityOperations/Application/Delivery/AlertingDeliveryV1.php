<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\AlertingEventType;

final readonly class AlertingDeliveryV1
{
    public function __construct(public AlertingEventType $type, public AlertingDeliveryPayload $payload) {}
}
