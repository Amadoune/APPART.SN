<?php

namespace Tests\PostgreSQL\Media;

use Appart\Modules\Media\Domain\Exception\ConcurrentMediaCollectionModification;
use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionTransaction;
use Closure;
use PDO;
use Throwable;

final class FailBeforeCommitMediaCollectionTransaction implements MediaCollectionTransaction
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
                throw new ConcurrentMediaCollectionModification;
            } $this->connection->commit();

            return $result;
        } catch (Throwable $error) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }throw $error;
        }
    }
}
