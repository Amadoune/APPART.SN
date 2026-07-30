<?php

namespace Appart\Modules\Media\Application\IngestionPersistence\Contract;

use Appart\Modules\Media\Application\IngestionPersistence\MediaAssetState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;

interface MediaAssetStore
{
    public function read(string $assetId): ?MediaAssetState;

    public function save(MediaAssetState $state, int $expectedVersion): MediaIngestionPersistenceWriteResult;
}
