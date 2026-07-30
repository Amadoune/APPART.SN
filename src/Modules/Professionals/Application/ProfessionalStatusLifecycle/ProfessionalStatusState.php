<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle;

enum ProfessionalStatusState: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}
