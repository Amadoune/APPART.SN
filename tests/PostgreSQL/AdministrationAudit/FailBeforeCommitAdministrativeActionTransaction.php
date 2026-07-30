<?php

namespace Tests\PostgreSQL\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Domain\Exception\ConcurrentAdministrativeActionModification;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionTransaction;
use Closure;
use PDO;
use Throwable;

final class FailBeforeCommitAdministrativeActionTransaction implements AdministrativeActionTransaction
{
    private bool $failNext = false;

    public function __construct(private readonly PDO $connection) {}

    public function failNext(): void
    {
        $this->failNext = true;
    }

    public function run(Closure $operation): mixed
    {
        $this->connection->beginTransaction();
        try {
            $result = $operation();
            if ($this->failNext) {
                $this->failNext = false;
                throw new ConcurrentAdministrativeActionModification;
            }
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
