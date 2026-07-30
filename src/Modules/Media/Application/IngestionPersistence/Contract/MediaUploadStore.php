<?php

namespace Appart\Modules\Media\Application\IngestionPersistence\Contract;

use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Application\IngestionPersistence\MediaUploadState;

interface MediaUploadStore
{
    public function read(string $uploadId): ?MediaUploadState;

    public function save(MediaUploadState $state, int $expectedVersion): MediaIngestionPersistenceWriteResult;
}
