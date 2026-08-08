<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

final readonly class UserAcceptanceEventV1
{
    public function __construct(public UserAcceptanceEventType $type, public UserAcceptanceEventPayload $payload) {}
}
