<?php

namespace Tests\PostgreSQL\SearchQueryResolutionOwnerSourceRuntime;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchQuery;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionRevisionDecision;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionRevisionState;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\DeterministicSearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\DeterministicSearchQueryResolutionOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSourceRuntime\SearchQueryResolutionOwnerSourceRuntimeAvailability;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchQueryResolutionOwnerSource;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchQueryResolutionOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlSearchQueryResolutionOwnerSourceRuntimeTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->migrate();
        $this->connection->exec('TRUNCATE search_discovery.search_query_resolution_current_index, search_discovery.search_query_resolution_revision_journal');
    }

    public function test_runtime_reports_availability_failure_recovery_and_corruption(): void
    {
        $runtime = $this->runtime();
        self::assertSame(SearchQueryResolutionOwnerSourceRuntimeAvailability::Available, $runtime->availability());
        $this->connection->exec('DROP TABLE search_discovery.search_query_resolution_current_index, search_discovery.search_query_resolution_revision_journal');
        self::assertSame(SearchQueryResolutionOwnerSourceRuntimeAvailability::DependencyUnavailable, $runtime->availability());
        $this->migrate();
        self::assertSame(SearchQueryResolutionOwnerSourceRuntimeAvailability::Available, $runtime->availability());

        $this->source()->append(new SearchQueryResolutionRevisionState(
            new SearchQuery('runtime-health-probe'),
            1,
            SearchQueryResolutionRevisionDecision::Found,
            new DateTimeImmutable('2026-08-01T08:00:00Z'),
            new DateTimeImmutable('2026-08-01T08:00:00Z'),
        ));
        $this->connection->exec("UPDATE search_discovery.search_query_resolution_revision_journal SET revision_checksum=repeat('0',64)");
        self::assertSame(SearchQueryResolutionOwnerSourceRuntimeAvailability::Corrupted, $runtime->availability());
    }

    private function runtime(): DeterministicSearchQueryResolutionOwnerSourceRuntimeV1
    {
        return new DeterministicSearchQueryResolutionOwnerSourceRuntimeV1(new DeterministicSearchQueryResolutionOwnerSourceRuntimeAvailabilityPolicy($this->source()));
    }

    private function source(): PostgreSqlSearchQueryResolutionOwnerSource
    {
        return new PostgreSqlSearchQueryResolutionOwnerSource($this->connection, new SearchQueryResolutionOwnerSourceMapper);
    }

    private function migrate(): void
    {
        $path = dirname(__DIR__, 3).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/076_search_query_resolution_owner_source.sql';
        $this->connection->exec((string) file_get_contents($path));
    }
}
