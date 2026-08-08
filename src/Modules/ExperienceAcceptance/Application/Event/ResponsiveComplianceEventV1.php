<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

final readonly class ResponsiveComplianceEventV1
{
    public function __construct(public ResponsiveComplianceEventType $type, public ResponsiveComplianceEventPayload $payload) {}
}
