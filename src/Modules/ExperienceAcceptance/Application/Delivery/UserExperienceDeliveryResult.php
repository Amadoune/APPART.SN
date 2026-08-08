<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

final readonly class UserExperienceDeliveryResult
{
    public function __construct(public UserExperienceDeliveryV1 $delivery) {}

    public function status(): UserExperienceDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
