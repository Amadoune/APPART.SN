<?php

namespace App\Infrastructure\ModerationAtomicOperation\PostgreSql;

use App\Application\ModerationAtomicOperation\Contract\ModerationAtomicOperationV1;
use App\Application\ModerationAtomicOperation\ModerationAtomicDecision;
use App\Application\ModerationAtomicOperation\ModerationAtomicWorkResult;
use PDO;
use Throwable;

final readonly class PostgreSqlModerationAtomicOperation implements ModerationAtomicOperationV1
{
    public function __construct(private PDO $connection) {}

    public function execute(callable $operation): ModerationAtomicWorkResult
    {
        $owner = ! $this->connection->inTransaction();
        $savepoint = 'moderation_atomic_operation';

        $owner
            ? $this->connection->beginTransaction()
            : $this->connection->exec("SAVEPOINT {$savepoint}");

        try {
            $result = $operation();

            if ($result->decision === ModerationAtomicDecision::Rollback) {
                $owner
                    ? $this->connection->rollBack()
                    : $this->connection->exec("ROLLBACK TO SAVEPOINT {$savepoint}");

                return $result;
            }

            $owner
                ? $this->connection->commit()
                : $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");

            return $result;
        } catch (Throwable $error) {
            $owner
                ? $this->connection->rollBack()
                : $this->connection->exec("ROLLBACK TO SAVEPOINT {$savepoint}");

            throw $error;
        }
    }
}
