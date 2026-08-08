<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\EndToEndReadinessEventV1;

final readonly class EndToEndReadinessDeliveryFactory
{
    public function create(EndToEndReadinessEventV1 $event): EndToEndReadinessDeliveryResult
    {
        return new EndToEndReadinessDeliveryResult(new EndToEndReadinessDeliveryV1($event->type, new EndToEndReadinessDeliveryPayload(EndToEndReadinessDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
