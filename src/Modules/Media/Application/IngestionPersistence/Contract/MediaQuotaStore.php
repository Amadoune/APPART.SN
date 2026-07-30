<?php

namespace Appart\Modules\Media\Application\IngestionPersistence\Contract;

use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Application\IngestionPersistence\MediaQuotaState;

interface MediaQuotaStore
{
    public function read(string $scopeId): ?MediaQuotaState;

    public function save(MediaQuotaState $state, int $expectedVersion): MediaIngestionPersistenceWriteResult;
}
