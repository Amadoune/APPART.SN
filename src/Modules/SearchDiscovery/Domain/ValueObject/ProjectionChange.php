<?php

namespace Appart\Modules\SearchDiscovery\Domain\ValueObject;

enum ProjectionChange: string
{
    case Indexed = 'indexed';
    case Reindexed = 'reindexed';
    case Hidden = 'hidden';
    case Removed = 'removed';
    case Rebuilt = 'rebuilt';
}
