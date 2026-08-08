<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\AccessibilityComplianceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;

final readonly class AccessibilityComplianceEventFactory
{
    public function __construct(private AccessibilityComplianceReaderV1 $reader) {}

    public function create(ExperienceAcceptanceObservedAt $observedAt): AccessibilityComplianceEventV1
    {
        $result = $this->reader->read($observedAt);

        return new AccessibilityComplianceEventV1(AccessibilityComplianceEventType::Observed, new AccessibilityComplianceEventPayload(AccessibilityComplianceEventStatus::from($result->status->value), $result->observedAt));
    }
}
