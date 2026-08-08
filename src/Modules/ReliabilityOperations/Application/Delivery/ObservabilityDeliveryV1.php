<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\ObservabilityEventType;

final readonly class ObservabilityDeliveryV1
{
    public function __construct(public ObservabilityEventType $type, public ObservabilityDeliveryPayload $payload) {}
}
