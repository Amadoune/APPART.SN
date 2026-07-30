<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationTransaction;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingTransaction;
use Closure;
use PDO;
use Throwable;

final readonly class PostgreSqlListingTransaction implements ListingCreationTransaction, ListingTransaction
{
    public function __construct(private PDO $connection) {}

    public function run(Closure $operation): mixed
    {
        $owner = ! $this->connection->inTransaction();
        $savepoint = 'listing_'.bin2hex(random_bytes(8));
        if ($owner) {
            $this->connection->beginTransaction();
        } else {
            $this->connection->exec("SAVEPOINT {$savepoint}");
        }
        try {
            $result = $operation();
            if ($owner) {
                $this->connection->commit();
            } else {
                $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");
            }

            return $result;
        } catch (Throwable $error) {
            $active = $this->connection->inTransaction();
            if ($owner) {
                if ($active) {
                    $this->connection->rollBack();
                }
            } elseif ($active) {
                $this->connection->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
                $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");
            }
            throw $error;
        }
    }
}
