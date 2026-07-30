<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycle;

enum MediaItemLifecycleWorkflowResult: string
{
    case Allowed = 'allowed';
    case Denied = 'denied';
}
