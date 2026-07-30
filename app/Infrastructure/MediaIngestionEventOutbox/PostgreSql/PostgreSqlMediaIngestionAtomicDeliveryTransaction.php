<?php

namespace App\Infrastructure\MediaIngestionEventOutbox\PostgreSql;

use Appart\Modules\Media\Application\MediaIngestionEventIntegration\Contract\MediaIngestionAtomicDeliveryTransaction;
use PDO;
use Throwable;

final readonly class PostgreSqlMediaIngestionAtomicDeliveryTransaction implements MediaIngestionAtomicDeliveryTransaction
{
    public function __construct(private PDO $connection) {}

    public function execute(callable $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        $savepoint = 'media_ingestion_atomic_delivery';
        if ($owner) {
            $this->connection->beginTransaction();
        } else {
            $this->connection->exec("SAVEPOINT {$savepoint}");
        }
        try {
            $result = $operation();
            if ($owner) {
                $this->connection->commit();
            } else {
                $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");
            }

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            } else {
                $this->connection->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
                $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");
            }
            throw $error;
        }
    }
}
