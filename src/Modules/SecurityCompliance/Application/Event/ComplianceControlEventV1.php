<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

final readonly class ComplianceControlEventV1
{
    public function __construct(public ComplianceControlEventType $type, public ComplianceControlEventPayload $payload) {}
}
