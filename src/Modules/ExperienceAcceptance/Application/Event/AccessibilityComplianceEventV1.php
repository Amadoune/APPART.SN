<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

final readonly class AccessibilityComplianceEventV1
{
    public function __construct(public AccessibilityComplianceEventType $type, public AccessibilityComplianceEventPayload $payload) {}
}
