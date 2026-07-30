<?php

namespace Tests\PostgreSQL\ReservationLifecycleWorkflow;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\Contract\ReservationLifecycleWorkflowStore;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationLifecycleWorkflowRepository;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationLifecycleWorkflowMapper;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\ReservationLifecycleWorkflow\ReservationLifecycleWorkflowStoreContract;

final class PostgreSqlReservationLifecycleWorkflowRepositoryContractTest extends ReservationLifecycleWorkflowStoreContract
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    protected function repository(): ReservationLifecycleWorkflowStore
    {
        return new PostgreSqlReservationLifecycleWorkflowRepository($this->connection, new ReservationLifecycleWorkflowMapper);
    }
}
