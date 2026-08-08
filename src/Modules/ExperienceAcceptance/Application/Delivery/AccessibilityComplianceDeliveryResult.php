<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

final readonly class AccessibilityComplianceDeliveryResult
{
    public function __construct(public AccessibilityComplianceDeliveryV1 $delivery) {}

    public function status(): AccessibilityComplianceDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
