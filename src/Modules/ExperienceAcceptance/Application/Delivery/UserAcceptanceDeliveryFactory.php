<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\UserAcceptanceEventV1;

final readonly class UserAcceptanceDeliveryFactory
{
    public function create(UserAcceptanceEventV1 $event): UserAcceptanceDeliveryResult
    {
        return new UserAcceptanceDeliveryResult(new UserAcceptanceDeliveryV1($event->type, new UserAcceptanceDeliveryPayload(UserAcceptanceDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
