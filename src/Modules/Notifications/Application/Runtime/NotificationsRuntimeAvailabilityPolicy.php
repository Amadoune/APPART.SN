<?php

namespace Appart\Modules\Notifications\Application\Runtime;

interface NotificationsRuntimeAvailabilityPolicy
{
    public function inspect(): NotificationsRuntimeAvailability;
}
