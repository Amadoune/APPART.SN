<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionPersistenceTransactionMode;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\Contract\AdministrativeActionLifecycleAtomicPersistenceTransaction;
use Closure;
use PDO;
use Throwable;

final readonly class PostgreSqlAdministrativeActionLifecycleAtomicPersistenceTransaction implements AdministrativeActionLifecycleAtomicPersistenceTransaction
{
    public function __construct(private PDO $connection) {}

    public function mode(): AdministrativeActionPersistenceTransactionMode
    {
        return $this->connection->inTransaction()
            ? AdministrativeActionPersistenceTransactionMode::External
            : AdministrativeActionPersistenceTransactionMode::Local;
    }

    public function run(Closure $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }

        try {
            $result = $operation();
            if ($owner) {
                $this->connection->commit();
            }

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }

            throw $error;
        }
    }
}
