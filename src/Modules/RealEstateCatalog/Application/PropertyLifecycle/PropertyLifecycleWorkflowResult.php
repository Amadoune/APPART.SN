<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

enum PropertyLifecycleWorkflowResult: string
{
    case Allowed = 'allowed';
    case Denied = 'denied';
}
