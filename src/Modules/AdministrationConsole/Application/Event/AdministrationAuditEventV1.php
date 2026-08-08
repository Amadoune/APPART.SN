<?php

namespace Appart\Modules\AdministrationConsole\Application\Event;

final readonly class AdministrationAuditEventV1
{
    public function __construct(public AdministrationAuditEventType $type, public AdministrationAuditEventPayload $payload) {}
}
