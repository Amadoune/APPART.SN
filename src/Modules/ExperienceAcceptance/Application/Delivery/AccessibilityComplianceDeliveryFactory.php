<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\AccessibilityComplianceEventV1;

final readonly class AccessibilityComplianceDeliveryFactory
{
    public function create(AccessibilityComplianceEventV1 $event): AccessibilityComplianceDeliveryResult
    {
        return new AccessibilityComplianceDeliveryResult(new AccessibilityComplianceDeliveryV1($event->type, new AccessibilityComplianceDeliveryPayload(AccessibilityComplianceDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
