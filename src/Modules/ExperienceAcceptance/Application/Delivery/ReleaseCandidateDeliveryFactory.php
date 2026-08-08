<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\ReleaseCandidateEventV1;

final readonly class ReleaseCandidateDeliveryFactory
{
    public function create(ReleaseCandidateEventV1 $event): ReleaseCandidateDeliveryResult
    {
        return new ReleaseCandidateDeliveryResult(new ReleaseCandidateDeliveryV1($event->type, new ReleaseCandidateDeliveryPayload(ReleaseCandidateDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
