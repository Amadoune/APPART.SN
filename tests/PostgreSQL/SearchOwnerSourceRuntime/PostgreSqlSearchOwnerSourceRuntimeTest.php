<?php

namespace Tests\PostgreSQL\SearchOwnerSourceRuntime;

use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerRevisionDecision;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerRevisionState;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\DeterministicSearchOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\DeterministicSearchOwnerSourceRuntimeV1;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\SearchOwnerSourceRuntimeAvailability;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchDocumentId;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchOwnerSource;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\SearchDecisionFixture;

final class PostgreSqlSearchOwnerSourceRuntimeTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->migrate();
        $this->connection->exec('TRUNCATE search_discovery.search_owner_current_index, search_discovery.search_owner_revision_journal');
    }

    public function test_runtime_reports_availability_failure_recovery_and_corruption(): void
    {
        $runtime = $this->runtime();
        self::assertSame(SearchOwnerSourceRuntimeAvailability::Available, $runtime->availability());

        $this->connection->exec('DROP TABLE search_discovery.search_owner_current_index, search_discovery.search_owner_revision_journal');
        self::assertSame(SearchOwnerSourceRuntimeAvailability::DependencyUnavailable, $runtime->availability());

        $this->migrate();
        self::assertSame(SearchOwnerSourceRuntimeAvailability::Available, $runtime->availability());

        $source = $this->source();
        $source->append(new SearchOwnerRevisionState(
            SearchDocumentId::fromString('00000000-0000-4000-8000-000000005501'),
            1,
            SearchOwnerRevisionDecision::Visible,
            SearchDecisionFixture::make(),
            new DateTimeImmutable('2026-08-01T08:00:00Z'),
            new DateTimeImmutable('2026-08-01T08:00:00Z'),
        ));
        $this->connection->exec("UPDATE search_discovery.search_owner_revision_journal SET revision_checksum=repeat('0',64)");
        self::assertSame(SearchOwnerSourceRuntimeAvailability::Corrupted, $runtime->availability());
    }

    private function runtime(): DeterministicSearchOwnerSourceRuntimeV1
    {
        return new DeterministicSearchOwnerSourceRuntimeV1(new DeterministicSearchOwnerSourceRuntimeAvailabilityPolicy($this->source()));
    }

    private function source(): PostgreSqlSearchOwnerSource
    {
        return new PostgreSqlSearchOwnerSource($this->connection, new SearchOwnerSourceMapper);
    }

    private function migrate(): void
    {
        $path = dirname(__DIR__, 3).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/075_search_owner_source.sql';
        $this->connection->exec((string) file_get_contents($path));
    }
}
