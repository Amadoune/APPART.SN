<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusEvent;

enum ProfessionalStatusEventType: string
{
    case Suspended = 'professional.status.suspended';
    case Reactivated = 'professional.status.reactivated';
}
