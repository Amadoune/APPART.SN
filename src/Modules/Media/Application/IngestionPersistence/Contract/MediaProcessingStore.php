<?php

namespace Appart\Modules\Media\Application\IngestionPersistence\Contract;

use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Application\IngestionPersistence\MediaProcessingState;

interface MediaProcessingStore
{
    public function read(string $assetId): ?MediaProcessingState;

    public function save(MediaProcessingState $state, int $expectedVersion): MediaIngestionPersistenceWriteResult;
}
