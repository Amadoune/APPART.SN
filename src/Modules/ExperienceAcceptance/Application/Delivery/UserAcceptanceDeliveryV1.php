<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Delivery;

use Appart\Modules\ExperienceAcceptance\Application\Event\UserAcceptanceEventType;

final readonly class UserAcceptanceDeliveryV1
{
    public function __construct(public UserAcceptanceEventType $type, public UserAcceptanceDeliveryPayload $payload) {}
}
