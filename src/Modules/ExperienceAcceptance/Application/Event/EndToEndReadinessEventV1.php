<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

final readonly class EndToEndReadinessEventV1
{
    public function __construct(public EndToEndReadinessEventType $type, public EndToEndReadinessEventPayload $payload) {}
}
