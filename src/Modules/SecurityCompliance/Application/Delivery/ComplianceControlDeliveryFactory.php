<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

use Appart\Modules\SecurityCompliance\Application\Event\ComplianceControlEventV1;

final readonly class ComplianceControlDeliveryFactory
{
    public function create(ComplianceControlEventV1 $event): ComplianceControlDeliveryResult
    {
        return new ComplianceControlDeliveryResult(new ComplianceControlDeliveryV1($event->type, new ComplianceControlDeliveryPayload(ComplianceControlDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
