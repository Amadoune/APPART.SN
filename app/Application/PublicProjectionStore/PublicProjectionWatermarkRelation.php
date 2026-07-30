<?php

namespace App\Application\PublicProjectionStore;

enum PublicProjectionWatermarkRelation: string
{
    case Equal = 'equal';
    case Newer = 'newer';
    case Older = 'older';
    case Incomparable = 'incomparable';
    case Incomplete = 'incomplete';
}
