<?php

namespace Appart\Modules\ContentSeo\Application\Delivery;

final readonly class EditorialContentDeliveryResult
{
    public function __construct(public EditorialContentDeliveryV1 $delivery) {}

    public function status(): EditorialContentDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
