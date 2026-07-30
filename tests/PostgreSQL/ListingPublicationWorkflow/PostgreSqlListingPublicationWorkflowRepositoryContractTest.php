<?php

namespace Tests\PostgreSQL\ListingPublicationWorkflow;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingPublicationWorkflowMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationWorkflowRepository;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\ListingPublicationWorkflow\ListingPublicationWorkflowRepositoryContract;

final class PostgreSqlListingPublicationWorkflowRepositoryContractTest extends ListingPublicationWorkflowRepositoryContract
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    protected function repository(): ListingPublicationWorkflowStore
    {
        return new PostgreSqlListingPublicationWorkflowRepository($this->connection, new ListingPublicationWorkflowMapper);
    }
}
