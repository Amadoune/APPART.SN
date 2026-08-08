<?php

namespace Appart\Modules\SecurityCompliance\Application\Delivery;

use Appart\Modules\SecurityCompliance\Application\Event\PrivacyPolicyEventV1;

final readonly class PrivacyPolicyDeliveryFactory
{
    public function create(PrivacyPolicyEventV1 $event): PrivacyPolicyDeliveryResult
    {
        return new PrivacyPolicyDeliveryResult(new PrivacyPolicyDeliveryV1($event->type, new PrivacyPolicyDeliveryPayload(PrivacyPolicyDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
