<?php

namespace Appart\Modules\Media\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Media\Application\IngestionPersistence\Contract\MediaUploadStore;
use Appart\Modules\Media\Application\IngestionPersistence\MediaIngestionPersistenceWriteResult;
use Appart\Modules\Media\Application\IngestionPersistence\MediaUploadState;
use Appart\Modules\Media\Infrastructure\Persistence\MediaIngestionStateMapper;

final readonly class PostgreSqlMediaUploadStore extends AbstractPostgreSqlMediaIngestionStore implements MediaUploadStore
{
    public function __construct(\PDO $connection, private MediaIngestionStateMapper $mapper)
    {
        parent::__construct($connection);
    }

    public function read(string $uploadId): ?MediaUploadState
    {
        $row = $this->row('uploads', $uploadId);

        return $row === null ? null : $this->mapper->upload($row);
    }

    public function save(MediaUploadState $state, int $expectedVersion): MediaIngestionPersistenceWriteResult
    {
        return $this->persist('uploads', 'upload_intents', $state, $expectedVersion, $this->read(...));
    }
}
