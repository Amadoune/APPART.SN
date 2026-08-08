<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

use Appart\Modules\SecurityCompliance\Application\Event\PrivacyPolicyEventType;

final readonly class PrivacyPolicyDeliveryV1
{
    public function __construct(public PrivacyPolicyEventType $type, public PrivacyPolicyDeliveryPayload $payload) {}
}
