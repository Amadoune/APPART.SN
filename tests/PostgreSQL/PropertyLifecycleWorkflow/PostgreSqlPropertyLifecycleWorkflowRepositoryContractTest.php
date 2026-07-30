<?php

namespace Tests\PostgreSQL\PropertyLifecycleWorkflow;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleWorkflowStore;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyLifecycleWorkflowRepository;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyLifecycleWorkflowMapper;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\PropertyLifecycleWorkflow\PropertyLifecycleWorkflowStoreContract;

final class PostgreSqlPropertyLifecycleWorkflowRepositoryContractTest extends PropertyLifecycleWorkflowStoreContract
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    protected function repository(): PropertyLifecycleWorkflowStore
    {
        return new PostgreSqlPropertyLifecycleWorkflowRepository($this->connection, new PropertyLifecycleWorkflowMapper);
    }
}
