<?php

namespace Appart\Modules\Geography\Application\PlaceMergeContext;

enum PlaceMergeObservedState: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
    case Merged = 'merged';
}
