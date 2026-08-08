<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

final readonly class SecretInventoryDeliveryResult
{
    public function __construct(public SecretInventoryDeliveryV1 $delivery) {}

    public function status(): SecretInventoryDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
