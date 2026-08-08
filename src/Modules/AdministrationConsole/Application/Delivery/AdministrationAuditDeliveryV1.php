<?php

namespace Appart\Modules\AdministrationConsole\Application\Delivery;

use Appart\Modules\AdministrationConsole\Application\Event\AdministrationAuditEventType;

final readonly class AdministrationAuditDeliveryV1
{
    public function __construct(public AdministrationAuditEventType $type, public AdministrationAuditDeliveryPayload $payload) {}
}
