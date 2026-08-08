<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\UserExperienceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;

final readonly class UserExperienceEventFactory
{
    public function __construct(private UserExperienceReaderV1 $reader) {}

    public function create(ExperienceAcceptanceObservedAt $observedAt): UserExperienceEventV1
    {
        $result = $this->reader->read($observedAt);

        return new UserExperienceEventV1(UserExperienceEventType::Observed, new UserExperienceEventPayload(UserExperienceEventStatus::from($result->status->value), $result->observedAt));
    }
}
