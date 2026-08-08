<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

use Appart\Modules\SecurityCompliance\Application\Event\SecurityAuditEventV1;

final readonly class SecurityAuditDeliveryFactory
{
    public function create(SecurityAuditEventV1 $event): SecurityAuditDeliveryResult
    {
        return new SecurityAuditDeliveryResult(new SecurityAuditDeliveryV1($event->type, new SecurityAuditDeliveryPayload(SecurityAuditDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
