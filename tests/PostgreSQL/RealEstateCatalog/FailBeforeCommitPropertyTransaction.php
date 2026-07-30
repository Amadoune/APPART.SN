<?php

namespace Tests\PostgreSQL\RealEstateCatalog;

use Appart\Modules\RealEstateCatalog\Domain\Exception\ConcurrentPropertyModification;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyTransaction;
use Closure;
use PDO;
use Throwable;

final class FailBeforeCommitPropertyTransaction implements PropertyTransaction
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
                throw new ConcurrentPropertyModification;
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
