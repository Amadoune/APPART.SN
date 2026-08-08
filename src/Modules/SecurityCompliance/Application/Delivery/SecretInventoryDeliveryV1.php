<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

use Appart\Modules\SecurityCompliance\Application\Event\SecretInventoryEventType;

final readonly class SecretInventoryDeliveryV1
{
    public function __construct(public SecretInventoryEventType $type, public SecretInventoryDeliveryPayload $payload) {}
}
