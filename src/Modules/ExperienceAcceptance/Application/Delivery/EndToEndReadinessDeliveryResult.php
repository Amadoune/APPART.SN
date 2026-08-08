<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

final readonly class EndToEndReadinessDeliveryResult
{
    public function __construct(public EndToEndReadinessDeliveryV1 $delivery) {}

    public function status(): EndToEndReadinessDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
