<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

final readonly class ReleaseCandidateDeliveryResult
{
    public function __construct(public ReleaseCandidateDeliveryV1 $delivery) {}

    public function status(): ReleaseCandidateDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
