<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\ReleaseCandidateReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;

final readonly class ReleaseCandidateEventFactory
{
    public function __construct(private ReleaseCandidateReaderV1 $reader) {}

    public function create(ExperienceAcceptanceObservedAt $observedAt): ReleaseCandidateEventV1
    {
        $result = $this->reader->read($observedAt);

        return new ReleaseCandidateEventV1(ReleaseCandidateEventType::Observed, new ReleaseCandidateEventPayload(ReleaseCandidateEventStatus::from($result->status->value), $result->observedAt));
    }
}
