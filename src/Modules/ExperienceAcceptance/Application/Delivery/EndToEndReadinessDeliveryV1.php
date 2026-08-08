<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\EndToEndReadinessEventType;

final readonly class EndToEndReadinessDeliveryV1
{
    public function __construct(public EndToEndReadinessEventType $type, public EndToEndReadinessDeliveryPayload $payload) {}
}
