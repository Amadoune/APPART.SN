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
use RuntimeException;
use Throwable;

final readonly class PostgreSqlAggregateOutboxTransaction implements AccountStatusAtomicTransaction, AdministrativeActionLifecycleAtomicTransaction, LeadLifecycleAtomicTransaction, ListingPublicationAtomicTransaction, MediaItemLifecycleAtomicTransaction, PlaceLifecycleAtomicTransaction, ProfessionalStatusAtomicTransaction, PropertyLifecycleAtomicTransaction, ReservationLifecycleAtomicTransaction
{
    public function __construct(private PDO $connection) {}

    public function run(Closure $aggregateAndOutboxWrites): mixed
    {
        if ($this->connection->inTransaction()) {
            throw new RuntimeException('Nested Aggregate + Outbox transactions are forbidden.');
        }
        $this->connection->beginTransaction();
        try {
            $result = $aggregateAndOutboxWrites();
            $this->connection->commit();

            return $result;
        } catch (Throwable $error) {
            $this->connection->rollBack();
            throw $error;
        }
    }
}
