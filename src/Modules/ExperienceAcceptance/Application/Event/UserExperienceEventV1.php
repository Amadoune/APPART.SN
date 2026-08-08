<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

final readonly class UserExperienceEventV1
{
    public function __construct(public UserExperienceEventType $type, public UserExperienceEventPayload $payload) {}
}
