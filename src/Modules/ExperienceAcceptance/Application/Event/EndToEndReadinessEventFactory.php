<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Event;

use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\EndToEndReadinessReaderV1;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\ExperienceAcceptanceObservedAt;

final readonly class EndToEndReadinessEventFactory
{
    public function __construct(private EndToEndReadinessReaderV1 $reader) {}

    public function create(ExperienceAcceptanceObservedAt $observedAt): EndToEndReadinessEventV1
    {
        $result = $this->reader->read($observedAt);

        return new EndToEndReadinessEventV1(EndToEndReadinessEventType::Observed, new EndToEndReadinessEventPayload(EndToEndReadinessEventStatus::from($result->status->value), $result->observedAt));
    }
}
