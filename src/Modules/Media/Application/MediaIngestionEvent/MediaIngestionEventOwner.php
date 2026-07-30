<?php

namespace Appart\Modules\Media\Application\MediaIngestionEvent;

enum MediaIngestionEventOwner: string
{
    case Asset = 'MediaIngestion.Asset';
}
