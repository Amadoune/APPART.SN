<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

final readonly class ReleaseCandidateEventV1
{
    public function __construct(public ReleaseCandidateEventType $type, public ReleaseCandidateEventPayload $payload) {}
}
