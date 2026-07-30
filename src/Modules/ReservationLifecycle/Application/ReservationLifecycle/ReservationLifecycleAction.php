<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle;

enum ReservationLifecycleAction: string
{
    case Submit = 'submit';
    case Confirm = 'confirm';
    case Reject = 'reject';
    case Start = 'start';
    case Complete = 'complete';
    case Cancel = 'cancel';
    case Expire = 'expire';
    case Unknown = 'unknown';
}
