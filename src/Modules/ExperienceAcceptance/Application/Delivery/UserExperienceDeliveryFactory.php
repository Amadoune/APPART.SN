<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\UserExperienceEventV1;

final readonly class UserExperienceDeliveryFactory
{
    public function create(UserExperienceEventV1 $event): UserExperienceDeliveryResult
    {
        return new UserExperienceDeliveryResult(new UserExperienceDeliveryV1($event->type, new UserExperienceDeliveryPayload(UserExperienceDeliveryStatus::from($event->payload->status->value), $event->payload->observedAt)));
    }
}
