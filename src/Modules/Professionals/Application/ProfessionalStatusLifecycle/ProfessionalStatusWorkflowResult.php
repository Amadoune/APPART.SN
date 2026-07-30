<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle;

enum ProfessionalStatusWorkflowResult: string
{
    case Allowed = 'allowed';
    case Denied = 'denied';
}
