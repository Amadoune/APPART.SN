<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

final readonly class ServiceHealthEventV1
{
    public function __construct(public ServiceHealthEventType $type, public ServiceHealthEventPayload $payload) {}
}
