<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

final readonly class ObservabilityEventV1
{
    public function __construct(public ObservabilityEventType $type, public ObservabilityEventPayload $payload) {}
}
