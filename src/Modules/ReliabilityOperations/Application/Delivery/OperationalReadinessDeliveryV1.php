<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\OperationalReadinessEventType;

final readonly class OperationalReadinessDeliveryV1
{
    public function __construct(public OperationalReadinessEventType $type, public OperationalReadinessDeliveryPayload $payload) {}
}
