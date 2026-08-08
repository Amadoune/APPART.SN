<?php

namespace Appart\Modules\AdministrationConsole\Application\Delivery;

final readonly class AdministrationOperatorDeliveryResult
{
    public function __construct(public AdministrationOperatorDeliveryV1 $delivery) {}

    public function status(): AdministrationOperatorDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
