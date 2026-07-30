<?php

namespace Appart\Modules\Media\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaQuotaStore;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Application\IngestionPersistence\MediaQuotaState;
use Appart\Modules\Media\Infrastructure\Persistence\MediaIngestionStateMapper;

final readonly class PostgreSqlMediaQuotaStore extends AbstractPostgreSqlMediaIngestionStore implements MediaQuotaStore
{
    public function __construct(\PDO $connection, private MediaIngestionStateMapper $mapper)
    {
        parent::__construct($connection);
    }

    public function read(string $scopeId): ?MediaQuotaState
    {
        $row = $this->row('quotas', $scopeId);

        return $row === null ? null : $this->mapper->quota($row);
    }

    public function save(MediaQuotaState $state, int $expectedVersion): MediaIngestionPersistenceWriteResult
    {
        return $this->persist('quotas', 'quota_intents', $state, $expectedVersion, $this->read(...));
    }
}
