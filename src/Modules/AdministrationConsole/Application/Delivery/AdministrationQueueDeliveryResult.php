<?php

namespace Appart\Modules\AdministrationConsole\Application\Delivery;

final readonly class AdministrationQueueDeliveryResult
{
    public function __construct(public AdministrationQueueDeliveryV1 $delivery) {}

    public function status(): AdministrationQueueDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
