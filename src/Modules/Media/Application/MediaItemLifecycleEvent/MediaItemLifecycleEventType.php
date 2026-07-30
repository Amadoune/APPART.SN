<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleEvent;

enum MediaItemLifecycleEventType: string
{
    case Removed = 'media.item.lifecycle.removed';
    case Archived = 'media.item.lifecycle.archived';
}
