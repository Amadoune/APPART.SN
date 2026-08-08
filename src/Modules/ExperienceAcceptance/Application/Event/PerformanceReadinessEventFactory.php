<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\PerformanceReadinessReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;

final readonly class PerformanceReadinessEventFactory
{
    public function __construct(private PerformanceReadinessReaderV1 $reader) {}

    public function create(ExperienceAcceptanceObservedAt $observedAt): PerformanceReadinessEventV1
    {
        $result = $this->reader->read($observedAt);

        return new PerformanceReadinessEventV1(PerformanceReadinessEventType::Observed, new PerformanceReadinessEventPayload(PerformanceReadinessEventStatus::from($result->status->value), $result->observedAt));
    }
}
