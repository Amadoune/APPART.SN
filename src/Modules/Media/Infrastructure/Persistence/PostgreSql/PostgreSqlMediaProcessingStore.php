<?php

namespace Appart\Modules\Media\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaProcessingStore;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Application\IngestionPersistence\MediaProcessingState;
use Appart\Modules\Media\Infrastructure\Persistence\MediaIngestionStateMapper;

final readonly class PostgreSqlMediaProcessingStore extends AbstractPostgreSqlMediaIngestionStore implements MediaProcessingStore
{
    public function __construct(\PDO $connection, private MediaIngestionStateMapper $mapper)
    {
        parent::__construct($connection);
    }

    public function read(string $assetId): ?MediaProcessingState
    {
        $row = $this->row('processing', $assetId);

        return $row === null ? null : $this->mapper->processing($row);
    }

    public function save(MediaProcessingState $state, int $expectedVersion): MediaIngestionPersistenceWriteResult
    {
        return $this->persist('processing', 'processing_intents', $state, $expectedVersion, $this->read(...));
    }
}
