<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\PerformanceReadinessEventV1;

final readonly class PerformanceReadinessDeliveryFactory
{
    public function create(PerformanceReadinessEventV1 $event): PerformanceReadinessDeliveryResult
    {
        return new PerformanceReadinessDeliveryResult(new PerformanceReadinessDeliveryV1($event->type, new PerformanceReadinessDeliveryPayload(PerformanceReadinessDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
