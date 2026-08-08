<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

final readonly class PrivacyPolicyEventV1
{
    public function __construct(public PrivacyPolicyEventType $type, public PrivacyPolicyEventPayload $payload) {}
}
