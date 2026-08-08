<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

final readonly class ResponsiveComplianceDeliveryResult
{
    public function __construct(public ResponsiveComplianceDeliveryV1 $delivery) {}

    public function status(): ResponsiveComplianceDeliveryStatus
    {
        return $this->delivery->payload->status;
    }
}
