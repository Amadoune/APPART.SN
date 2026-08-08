<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\UserExperienceEventType;

final readonly class UserExperienceDeliveryV1
{
    public function __construct(public UserExperienceEventType $type, public UserExperienceDeliveryPayload $payload) {}
}
