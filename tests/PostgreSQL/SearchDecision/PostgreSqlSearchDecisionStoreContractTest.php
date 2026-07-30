<?php

namespace Tests\PostgreSQL\SearchDecision;

use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionReader;
use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionWriter;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchDecisionReader;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchDecisionWriter;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchDecisionMapper;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\SearchDecision\SearchDecisionStoreContract;

final class PostgreSqlSearchDecisionStoreContractTest extends SearchDecisionStoreContract
{
    private PDO $connection;

    private SearchDecisionMapper $mapper;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->mapper = new SearchDecisionMapper;
    }

    protected function reader(): SearchDecisionReader
    {
        return new PostgreSqlSearchDecisionReader($this->connection, $this->mapper);
    }

    protected function writer(): SearchDecisionWriter
    {
        return new PostgreSqlSearchDecisionWriter($this->connection, $this->mapper);
    }
}
