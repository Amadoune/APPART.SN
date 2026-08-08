<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

use Appart\Modules\SecurityCompliance\Application\Event\SecurityAuditEventType;

final readonly class SecurityAuditDeliveryV1
{
    public function __construct(public SecurityAuditEventType $type, public SecurityAuditDeliveryPayload $payload) {}
}
