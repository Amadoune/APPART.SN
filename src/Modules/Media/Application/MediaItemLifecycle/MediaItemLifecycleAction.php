<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycle;

enum MediaItemLifecycleAction: string
{
    case Remove = 'remove';
    case Archive = 'archive';
    case Unknown = 'unknown';
}
