<?php

namespace App\Application\MediaAuthoringHttp;

enum MediaAuthoringHttpOperation: string
{
    case Upload = 'upload';
    case Collection = 'collection';
    case Archive = 'archive';
}
