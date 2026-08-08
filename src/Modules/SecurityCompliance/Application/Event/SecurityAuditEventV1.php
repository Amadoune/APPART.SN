<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

final readonly class SecurityAuditEventV1
{
    public function __construct(public SecurityAuditEventType $type, public SecurityAuditEventPayload $payload) {}
}
