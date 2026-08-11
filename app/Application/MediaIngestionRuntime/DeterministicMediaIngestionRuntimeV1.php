<?php

namespace App\Application\MediaIngestionRuntime;

use App\Application\MediaIngestionRuntime\Contract\MediaIngestionRuntimeAvailabilityPolicy;
use App\Application\MediaIngestionRuntime\Contract\MediaIngestionRuntimeReport;
use App\Application\MediaIngestionRuntime\Contract\MediaIngestionRuntimeV1;
use Appart\Modules\Media\Application\Attachment\Contract\AttachReadyMediaAssetV1;
use Appart\Modules\Media\Application\BinaryStorage\Contract\MediaBinaryStorageAuthorityV1;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaProcessingStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaQuotaStore;
use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaUploadStore;
use Appart\Modules\Media\Application\ReadyAsset\Contract\MediaAssetReadinessV1;

final readonly class DeterministicMediaIngestionRuntimeV1 implements MediaIngestionRuntimeV1
{
    public function __construct(
        private AttachReadyMediaAssetV1 $attachment,
        private MediaBinaryStorageAuthorityV1 $binary,
        private MediaAssetReadinessV1 $readiness,
        private MediaUploadStore $upload,
        private MediaAssetStore $asset,
        private MediaProcessingStore $processing,
        private MediaQuotaStore $quota,
        private MediaIngestionRuntimeAvailabilityPolicy $availability,
    ) {}

    public function attachment(): AttachReadyMediaAssetV1
    {
        return $this->attachment;
    }

    public function binary(): MediaBinaryStorageAuthorityV1
    {
        return $this->binary;
    }

    public function readiness(): MediaAssetReadinessV1
    {
        return $this->readiness;
    }

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
