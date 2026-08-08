<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

final readonly class PerformanceReadinessEventV1
{
    public function __construct(public PerformanceReadinessEventType $type, public PerformanceReadinessEventPayload $payload) {}
}
