<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

final readonly class IncidentEventV1
{
    public function __construct(public IncidentEventType $type, public IncidentEventPayload $payload) {}
}
