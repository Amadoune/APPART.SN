<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle;

enum ReservationLifecycleWorkflowResult: string
{
    case Allowed = 'allowed';
    case Denied = 'denied';
}
