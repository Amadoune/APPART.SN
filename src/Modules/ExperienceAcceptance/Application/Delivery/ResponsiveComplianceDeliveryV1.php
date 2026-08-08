<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\ResponsiveComplianceEventType;

final readonly class ResponsiveComplianceDeliveryV1
{
    public function __construct(public ResponsiveComplianceEventType $type, public ResponsiveComplianceDeliveryPayload $payload) {}
}
