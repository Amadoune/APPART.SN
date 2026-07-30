<?php

namespace App\Application\PublicProjectionRebuild;

enum PublicProjectionRebuildScopeType: string
{
    case Full = 'full';
    case Listings = 'listings';
    case Range = 'range';
}
