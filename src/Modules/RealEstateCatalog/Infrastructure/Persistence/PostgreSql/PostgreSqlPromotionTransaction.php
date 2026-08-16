<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromotionTransaction;
use Closure;
use PDO;
use Throwable;

final readonly class PostgreSqlPromotionTransaction implements PromotionTransaction
{
    public function __construct(private PDO $connection) {}

    public function run(string $commandId, string $propertyId, Closure $operation): mixed
    {
        $this->connection->beginTransaction();
        try {
            $lock = $this->connection->prepare("SELECT pg_advisory_xact_lock(hashtextextended('property-promotion:' || :command_id,0)), pg_advisory_xact_lock(hashtextextended('property:' || :property_id,0)), pg_advisory_xact_lock(hashtextextended('property-authoring:' || :property_id,0))");
            $lock->execute(['command_id' => $commandId, 'property_id' => $propertyId]);
            $result = $operation();
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
