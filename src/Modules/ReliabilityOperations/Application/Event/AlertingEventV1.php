<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

final readonly class AlertingEventV1
{
    public function __construct(public AlertingEventType $type, public AlertingEventPayload $payload) {}
}
