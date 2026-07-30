<?php

namespace App\Application\MediaIngestionRuntime\Contract;

use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaProcessingStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaQuotaStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaUploadStore;

interface MediaIngestionRuntimeV1
{
    public function upload(): MediaUploadStore;

    public function asset(): MediaAssetStore;

    public function processing(): MediaProcessingStore;

    public function quota(): MediaQuotaStore;

    public function inspect(): MediaIngestionRuntimeReport;
}
