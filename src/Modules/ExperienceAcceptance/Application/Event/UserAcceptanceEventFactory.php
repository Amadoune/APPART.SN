<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\UserAcceptanceReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;

final readonly class UserAcceptanceEventFactory
{
    public function __construct(private UserAcceptanceReaderV1 $reader) {}

    public function create(ExperienceAcceptanceObservedAt $observedAt): UserAcceptanceEventV1
    {
        $result = $this->reader->read($observedAt);

        return new UserAcceptanceEventV1(UserAcceptanceEventType::Observed, new UserAcceptanceEventPayload(UserAcceptanceEventStatus::from($result->status->value), $result->observedAt));
    }
}
