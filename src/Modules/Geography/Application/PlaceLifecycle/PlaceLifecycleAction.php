<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycle;

enum PlaceLifecycleAction: string
{
    case Enable = 'enable';
    case Disable = 'disable';
    case Merge = 'merge';
}
