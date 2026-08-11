<?php

namespace Appart\Modules\Media\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionTransaction;
use Closure;
use PDO;
use Throwable;

final readonly class PostgreSqlMediaCollectionTransaction implements MediaCollectionTransaction
{
    public function __construct(private PDO $connection) {}

    public function run(Closure $operation): mixed
    {
        $external = $this->connection->inTransaction();
        if ($external) {
            $this->connection->exec('SAVEPOINT media_collection_repository');
        } else {
            $this->connection->beginTransaction();
        }
        try {
            $result = $operation();
            if ($external) {
                $this->connection->exec('RELEASE SAVEPOINT media_collection_repository');
            } else {
                $this->connection->commit();
            }

            return $result;
        } catch (Throwable $error) {
            if ($external && $this->connection->inTransaction()) {
                $this->connection->exec('ROLLBACK TO SAVEPOINT media_collection_repository');
            } elseif ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }
}
