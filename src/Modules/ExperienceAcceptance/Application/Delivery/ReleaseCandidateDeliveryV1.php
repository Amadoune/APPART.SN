<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\ReleaseCandidateEventType;

final readonly class ReleaseCandidateDeliveryV1
{
    public function __construct(public ReleaseCandidateEventType $type, public ReleaseCandidateDeliveryPayload $payload) {}
}
