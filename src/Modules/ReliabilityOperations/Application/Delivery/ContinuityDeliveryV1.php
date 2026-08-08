<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

use Appart\Modules\ReliabilityOperations\Application\Event\ContinuityEventType;

final readonly class ContinuityDeliveryV1
{
    public function __construct(public ContinuityEventType $type, public ContinuityDeliveryPayload $payload) {}
}
