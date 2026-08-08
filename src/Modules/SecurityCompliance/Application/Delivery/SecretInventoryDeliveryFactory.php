<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

use Appart\Modules\SecurityCompliance\Application\Event\SecretInventoryEventV1;

final readonly class SecretInventoryDeliveryFactory
{
    public function create(SecretInventoryEventV1 $event): SecretInventoryDeliveryResult
    {
        return new SecretInventoryDeliveryResult(new SecretInventoryDeliveryV1($event->type, new SecretInventoryDeliveryPayload(SecretInventoryDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
