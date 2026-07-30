<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycle;

enum MediaItemLifecycleState: string
{
    case Active = 'active';
    case Removed = 'removed';
    case Archived = 'archived';
}
