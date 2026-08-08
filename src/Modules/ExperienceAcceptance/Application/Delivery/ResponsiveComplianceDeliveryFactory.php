<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\ResponsiveComplianceEventV1;

final readonly class ResponsiveComplianceDeliveryFactory
{
    public function create(ResponsiveComplianceEventV1 $event): ResponsiveComplianceDeliveryResult
    {
        return new ResponsiveComplianceDeliveryResult(new ResponsiveComplianceDeliveryV1($event->type, new ResponsiveComplianceDeliveryPayload(ResponsiveComplianceDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
