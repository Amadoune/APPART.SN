<?php

namespace Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Geography\Infrastructure\Persistence\PlaceTransaction;
use Closure;
use PDO;
use Throwable;

final readonly class PostgreSqlPlaceTransaction implements PlaceTransaction
{
    public function __construct(private PDO $connection) {}

    public function run(Closure $operation): mixed
    {
        $this->connection->beginTransaction();
        try {
            $result = $operation();
            $this->connection->commit();

            return $result;
        } catch (Throwable $error) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }
}
