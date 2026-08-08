<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

final readonly class ContinuityEventV1
{
    public function __construct(public ContinuityEventType $type, public ContinuityEventPayload $payload) {}
}
