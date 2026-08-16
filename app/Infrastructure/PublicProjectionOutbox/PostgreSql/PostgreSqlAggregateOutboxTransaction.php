<?php

namespace App\Infrastructure\PublicProjectionOutbox\PostgreSql;

use App\Application\AccountStatusEventIntegration\Contract\AccountStatusAtomicTransaction;
use App\Application\AdministrativeActionLifecycleEventIntegration\Contract\AdministrativeActionLifecycleAtomicTransaction;
use App\Application\LeadLifecycleEventIntegration\Contract\LeadLifecycleAtomicTransaction;
use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationAtomicTransaction;
use App\Application\MediaItemLifecycleEventIntegration\Contract\MediaItemLifecycleAtomicTransaction;
use App\Application\PlaceLifecycleEventIntegration\Contract\PlaceLifecycleAtomicTransaction;
use App\Application\ProfessionalStatusEventIntegration\Contract\ProfessionalStatusAtomicTransaction;
use App\Application\PropertyLifecycleEventIntegration\Contract\PropertyLifecycleAtomicTransaction;
use App\Application\ReservationLifecycleEventIntegration\Contract\ReservationLifecycleAtomicTransaction;
use Closure;
use PDO;
use Throwable;

final readonly class PostgreSqlAggregateOutboxTransaction implements AccountStatusAtomicTransaction, AdministrativeActionLifecycleAtomicTransaction, LeadLifecycleAtomicTransaction, ListingPublicationAtomicTransaction, MediaItemLifecycleAtomicTransaction, PlaceLifecycleAtomicTransaction, ProfessionalStatusAtomicTransaction, PropertyLifecycleAtomicTransaction, ReservationLifecycleAtomicTransaction
{
    public function __construct(private PDO $connection) {}

    public function run(Closure $aggregateAndOutboxWrites): mixed
    {
        $owner = ! $this->connection->inTransaction();
        $savepoint = 'aggregate_outbox_'.bin2hex(random_bytes(8));
        $owner ? $this->connection->beginTransaction() : $this->connection->exec("SAVEPOINT {$savepoint}");
        try {
            $result = $aggregateAndOutboxWrites();
            $owner ? $this->connection->commit() : $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            } elseif ($this->connection->inTransaction()) {
                $this->connection->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
                $this->connection->exec("RELEASE SAVEPOINT {$savepoint}");
            }
            throw $error;
        }
    }
}
