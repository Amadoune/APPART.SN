<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\AccessibilityComplianceEventType;

final readonly class AccessibilityComplianceDeliveryV1
{
    public function __construct(public AccessibilityComplianceEventType $type, public AccessibilityComplianceDeliveryPayload $payload) {}
}
