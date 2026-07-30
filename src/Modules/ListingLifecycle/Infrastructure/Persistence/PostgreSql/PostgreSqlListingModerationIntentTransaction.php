<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ListingLifecycle\Application\ModerationIntent\Contract\ListingModerationIntentTransaction;
use Closure;
use PDO;
use Throwable;

final readonly class PostgreSqlListingModerationIntentTransaction implements ListingModerationIntentTransaction
{
    public function __construct(private PDO $connection) {}

    public function run(Closure $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        $savepoint = 'listing_moderation_intent';
        $owner
            ? $this->connection->beginTransaction()
            : $this->connection->exec("SAVEPOINT {$savepoint}");
        try {
            $result = $operation();
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
