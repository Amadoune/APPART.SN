<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\Contract\AccountStatusOrchestrationTransaction;
use PDO;
use Throwable;

final readonly class PostgreSqlAccountStatusOrchestrationTransaction implements AccountStatusOrchestrationTransaction
{
    public function __construct(private PDO $connection) {}

    public function run(callable $operation): AccountStatusOrchestrationResult
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
