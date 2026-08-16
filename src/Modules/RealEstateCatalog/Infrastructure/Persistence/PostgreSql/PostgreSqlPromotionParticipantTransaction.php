<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyTransaction;
use Closure;
use PDO;
use RuntimeException;

final readonly class PostgreSqlPromotionParticipantTransaction implements PropertyTransaction
{
    public function __construct(private PDO $connection) {}

    public function run(Closure $operation): mixed
    {
        if (! $this->connection->inTransaction()) {
            throw new RuntimeException('Property promotion persistence requires an active local transaction.');
        }

        return $operation();
    }
}
