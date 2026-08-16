<?php

namespace App\Application\PublicMediaSource;

enum PublicMediaSourceRevisionRelationV2
{
    case Equal;
    case Newer;
    case Older;
    case Incomparable;
}
