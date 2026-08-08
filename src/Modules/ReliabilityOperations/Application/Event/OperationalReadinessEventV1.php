<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

final readonly class OperationalReadinessEventV1
{
    public function __construct(public OperationalReadinessEventType $type, public OperationalReadinessEventPayload $payload) {}
}
