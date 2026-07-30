<?php

namespace App\Application\PublicMediaRevision;

enum PublicMediaRevisionRelation: string
{
    case Older = 'older';
    case Equal = 'equal';
    case Newer = 'newer';
}
