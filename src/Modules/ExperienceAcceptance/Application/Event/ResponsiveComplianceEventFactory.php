<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\ResponsiveComplianceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;

final readonly class ResponsiveComplianceEventFactory
{
    public function __construct(private ResponsiveComplianceReaderV1 $reader) {}

    public function create(ExperienceAcceptanceObservedAt $observedAt): ResponsiveComplianceEventV1
    {
        $result = $this->reader->read($observedAt);

        return new ResponsiveComplianceEventV1(ResponsiveComplianceEventType::Observed, new ResponsiveComplianceEventPayload(ResponsiveComplianceEventStatus::from($result->status->value), $result->observedAt));
    }
}
