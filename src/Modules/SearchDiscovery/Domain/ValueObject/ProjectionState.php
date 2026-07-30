<?php

namespace Appart\Modules\SearchDiscovery\Domain\ValueObject;

enum ProjectionState: string
{
    case Visible = 'visible';
    case Hidden = 'hidden';
    case Removed = 'removed';
}
