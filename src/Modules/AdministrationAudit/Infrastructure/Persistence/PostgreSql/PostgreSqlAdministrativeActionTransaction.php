<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionTransaction;
use Closure;
use PDO;
use Throwable;

final readonly class PostgreSqlAdministrativeActionTransaction implements AdministrativeActionTransaction
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
