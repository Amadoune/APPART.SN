<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle;

enum ProfessionalStatusAction: string
{
    case Suspend = 'suspend';
    case Reactivate = 'reactivate';
    case Unknown = 'unknown';
}
