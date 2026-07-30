<?php

namespace Appart\Modules\Media\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaAssetStore;
use Appart\Modules\Media\Application\IngestionPersistence\MediaAssetState;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Infrastructure\Persistence\MediaIngestionStateMapper;

final readonly class PostgreSqlMediaAssetStore extends AbstractPostgreSqlMediaIngestionStore implements MediaAssetStore
{
    public function __construct(\PDO $connection, private MediaIngestionStateMapper $mapper)
    {
        parent::__construct($connection);
    }

    public function read(string $assetId): ?MediaAssetState
    {
        $row = $this->row('assets', $assetId);

        return $row === null ? null : $this->mapper->asset($row);
    }

    public function save(MediaAssetState $state, int $expectedVersion): MediaIngestionPersistenceWriteResult
    {
        return $this->persist('assets', 'asset_intents', $state, $expectedVersion, $this->read(...));
    }
}
