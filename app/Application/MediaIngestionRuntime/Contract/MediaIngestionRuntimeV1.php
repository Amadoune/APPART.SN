<?php

namespace App\Application\MediaIngestionRuntime\Contract;

use Appart\Modules\Media\Application\Attachment\Contract\AttachReadyMediaAssetV1;
use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryStorageAuthorityV1;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaProcessingStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaQuotaStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaUploadStore;
use Appart\Modules\Media\Application\ReadyAsset\Contract\MediaAssetReadinessV1;

interface MediaIngestionRuntimeV1
{
    public function attachment(): AttachReadyMediaAssetV1;

    public function binary(): MediaBinaryStorageAuthorityV1;

    public function readiness(): MediaAssetReadinessV1;

    public function upload(): MediaUploadStore;

    public function asset(): MediaAssetStore;

    public function processing(): MediaProcessingStore;

    public function quota(): MediaQuotaStore;

    public function inspect(): MediaIngestionRuntimeReport;
}
