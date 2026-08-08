<?php

namespace Tests\PostgreSQL\SearchQueryResolutionOwnerSourceRuntimeRead;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\DeterministicSearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\DeterministicSearchQueryResolutionOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\DeterministicSearchQueryResolutionOwnerSourceRuntimeReadPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\DeterministicSearchQueryResolutionOwnerSourceRuntimeReadV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntimeRead\SearchQueryResolutionOwnerSourceRuntimeReadStatus;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchQueryResolutionOwnerSource;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchQueryResolutionOwnerSourceMapper;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlSearchQueryResolutionOwnerSourceRuntimeReadTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->migrate();
        $this->connection->exec('TRUNCATE search_discovery.search_query_resolution_current_index, search_discovery.search_query_resolution_revision_journal');
    }

    public function test_runtime_read_reports_found_failure_and_recovery(): void
    {
        $runtimeRead = $this->runtimeRead();
        self::assertSame(SearchQueryResolutionOwnerSourceRuntimeReadStatus::Found, $runtimeRead->read()->status);
        $this->connection->exec('DROP TABLE search_discovery.search_query_resolution_current_index, search_discovery.search_query_resolution_revision_journal');
        self::assertSame(SearchQueryResolutionOwnerSourceRuntimeReadStatus::DependencyUnavailable, $runtimeRead->read()->status);
        $this->migrate();
        self::assertSame(SearchQueryResolutionOwnerSourceRuntimeReadStatus::Found, $runtimeRead->read()->status);
    }

    private function runtimeRead(): DeterministicSearchQueryResolutionOwnerSourceRuntimeReadV1
    {
        $source = new PostgreSqlSearchQueryResolutionOwnerSource($this->connection, new SearchQueryResolutionOwnerSourceMapper);
        $runtime = new DeterministicSearchQueryResolutionOwnerSourceRuntimeV1(new DeterministicSearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy($source));

        return new DeterministicSearchQueryResolutionOwnerSourceRuntimeReadV1($runtime, new DeterministicSearchQueryResolutionOwnerSourceRuntimeReadPolicy);
    }

    private function migrate(): void
    {
        $path = dirname(__DIR__, 3).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/076_search_query_resolution_owner_source.sql';
        $this->connection->exec((string) file_get_contents($path));
    }
}
