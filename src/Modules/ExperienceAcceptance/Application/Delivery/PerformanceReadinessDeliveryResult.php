<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

final readonly class PerformanceReadinessDeliveryResult
{
    public function __construct(public PerformanceReadinessDeliveryV1 $delivery) {}

    public function status(): PerformanceReadinessDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
