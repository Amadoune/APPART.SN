<?php

namespace Appart\Modules\SecurityCompliance\Application\Event;

final readonly class SecretInventoryEventV1
{
    public function __construct(public SecretInventoryEventType $type, public SecretInventoryEventPayload $payload) {}
}
