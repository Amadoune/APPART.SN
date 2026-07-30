<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle;

enum ProfessionalStatusDiagnostic: string
{
    case IncompatibleState = 'incompatible_state';
    case UnknownAction = 'unknown_action';
}
