<?php

namespace App\Application\MediaIngestionRuntime;

use App\Application\MediaIngestionRuntime\Contract\MediaIngestionRuntimeAvailabilityPolicy;
use App\Application\MediaIngestionRuntime\Contract\MediaIngestionRuntimeReport;
use App\Application\MediaIngestionRuntime\Contract\MediaIngestionRuntimeV1;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaProcessingStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaQuotaStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaUploadStore;

final readonly class DeterministicMediaIngestionRuntimeV1 implements MediaIngestionRuntimeV1
{
    public function __construct(
        private MediaUploadStore $upload,
        private MediaAssetStore $asset,
        private MediaProcessingStore $processing,
        private MediaQuotaStore $quota,
        private MediaIngestionRuntimeAvailabilityPolicy $availability,
    ) {}

    public function upload(): MediaUploadStore
    {
        return $this->upload;
    }

    public function asset(): MediaAssetStore
    {
        return $this->asset;
    }

    public function processing(): MediaProcessingStore
    {
        return $this->processing;
    }

    public function quota(): MediaQuotaStore
    {
        return $this->quota;
    }

    public function inspect(): MediaIngestionRuntimeReport
    {
        return $this->availability->inspect();
    }
}
