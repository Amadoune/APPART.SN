<?php

namespace App\Infrastructure\PublicProjectionOutbox\PostgreSql;

use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionTransaction;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingTransaction;
use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionTransaction;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyTransaction;
use Closure;
use PDO;
use RuntimeException;

final readonly class PostgreSqlAggregateOutboxParticipantTransaction implements AdministrativeActionTransaction, ListingTransaction, MediaCollectionTransaction, PropertyTransaction
{
    public function __construct(private PDO $connection) {}

    public function run(Closure $operation): mixed
    {
        if (! $this->connection->inTransaction()) {
            throw new RuntimeException('Aggregate persistence must participate in an active Aggregate + Outbox transaction.');
        }

        return $operation();
    }
}
