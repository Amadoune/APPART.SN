<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycle;

enum PlaceLifecycleState: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
    case Merged = 'merged';
}
