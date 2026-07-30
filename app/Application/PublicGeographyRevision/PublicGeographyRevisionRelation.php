<?php

namespace App\Application\PublicGeographyRevision;

enum PublicGeographyRevisionRelation: string
{
    case Older = 'older';
    case Equal = 'equal';
    case Newer = 'newer';
}
