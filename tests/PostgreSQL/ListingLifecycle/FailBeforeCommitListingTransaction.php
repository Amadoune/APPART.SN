<?php

namespace Tests\PostgreSQL\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Domain\Exception\ConcurrentListingModification;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingTransaction;
use Closure;
use PDO;
use Throwable;

final class FailBeforeCommitListingTransaction implements ListingTransaction
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
                throw new ConcurrentListingModification;
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
