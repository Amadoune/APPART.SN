<?php

namespace Appart\Modules\Media\Application\MediaIngestionEvent;

enum MediaIngestionEventType: string
{
    case AssetReady = 'media.ingestion.asset.ready.v1';
    case AssetRejected = 'media.ingestion.asset.rejected.v1';
    case AssetPurged = 'media.ingestion.asset.purged.v1';
}
