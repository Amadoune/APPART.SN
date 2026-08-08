<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

use Appart\Modules\SecurityCompliance\Application\Event\ComplianceControlEventType;

final readonly class ComplianceControlDeliveryV1
{
    public function __construct(public ComplianceControlEventType $type, public ComplianceControlDeliveryPayload $payload) {}
}
