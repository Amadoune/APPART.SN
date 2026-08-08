<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\ServiceHealthEventType;

final readonly class ServiceHealthDeliveryV1
{
    public function __construct(public ServiceHealthEventType $type, public ServiceHealthDeliveryPayload $payload) {}
}
