<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

final readonly class UserAcceptanceDeliveryResult
{
    public function __construct(public UserAcceptanceDeliveryV1 $delivery) {}

    public function status(): UserAcceptanceDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
