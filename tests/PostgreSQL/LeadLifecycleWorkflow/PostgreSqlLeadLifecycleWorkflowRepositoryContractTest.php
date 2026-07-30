<?php

namespace Tests\PostgreSQL\LeadLifecycleWorkflow;

use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\Contract\LeadLifecycleWorkflowStore;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleWorkflowRepository;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\LeadLifecycleWorkflow\LeadLifecycleWorkflowStoreContract;

final class PostgreSqlLeadLifecycleWorkflowRepositoryContractTest extends LeadLifecycleWorkflowStoreContract
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    protected function repository(): LeadLifecycleWorkflowStore
    {
        return new PostgreSqlLeadLifecycleWorkflowRepository($this->connection, new LeadLifecycleWorkflowMapper);
    }
}
