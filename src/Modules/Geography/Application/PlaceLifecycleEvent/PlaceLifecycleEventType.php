<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycleEvent;

enum PlaceLifecycleEventType: string
{
    case Enabled = 'place.lifecycle.enabled';
    case Disabled = 'place.lifecycle.disabled';
    case Merged = 'place.lifecycle.merged';
}
