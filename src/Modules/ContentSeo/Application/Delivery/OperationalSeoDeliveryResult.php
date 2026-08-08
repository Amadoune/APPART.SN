<?php

namespace Appart\Modules\ContentSeo\Application\Delivery;

final readonly class OperationalSeoDeliveryResult
{
    public function __construct(public OperationalSeoDeliveryV1 $delivery) {}

    public function status(): OperationalSeoDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
