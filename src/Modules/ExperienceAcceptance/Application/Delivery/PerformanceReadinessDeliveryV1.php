<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\PerformanceReadinessEventType;

final readonly class PerformanceReadinessDeliveryV1
{
    public function __construct(public PerformanceReadinessEventType $type, public PerformanceReadinessDeliveryPayload $payload) {}
}
