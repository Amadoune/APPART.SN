<?php

namespace Tests\PostgreSQL\SearchOwnerSourceRuntimeRead;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\DeterministicSearchOwnerSourceRuntimeReadPolicy;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\DeterministicSearchOwnerSourceRuntimeReadV1;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\SearchOwnerSourceRuntimeReadAvailability;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\SearchOwnerSourceRuntimeReadStatus;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchOwnerSource;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\SearchDiscovery\SearchOwnerSource\SearchOwnerSourceMapperTest;

final class PostgreSqlSearchOwnerSourceRuntimeReadTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->migrate();
        $this->connection->exec('TRUNCATE search_discovery.search_owner_current_index, search_discovery.search_owner_revision_journal');
    }

    public function test_runtime_read_propagates_allowed_empty_corruption_failure_and_recovery(): void
    {
        $runtime = $this->runtime();
        self::assertSame(SearchOwnerSourceRuntimeReadStatus::Empty, $runtime->read(SearchOwnerSourceMapperTest::document(), $this->observedAt())->status);
        $this->source()->append(SearchOwnerSourceMapperTest::state());
        self::assertSame(SearchOwnerSourceRuntimeReadStatus::Allowed, $runtime->read(SearchOwnerSourceMapperTest::document(), $this->observedAt())->status);
        $this->connection->exec("UPDATE search_discovery.search_owner_revision_journal SET revision_checksum=repeat('0',64)");
        self::assertSame(SearchOwnerSourceRuntimeReadStatus::Corrupted, $runtime->read(SearchOwnerSourceMapperTest::document(), $this->observedAt())->status);
        $this->connection->exec('DROP TABLE search_discovery.search_owner_current_index, search_discovery.search_owner_revision_journal');
        self::assertSame(SearchOwnerSourceRuntimeReadStatus::DependencyUnavailable, $runtime->read(SearchOwnerSourceMapperTest::document(), $this->observedAt())->status);
        self::assertSame(SearchOwnerSourceRuntimeReadAvailability::DependencyUnavailable, $runtime->diagnostics()->availability);
        $this->migrate();
        self::assertSame(SearchOwnerSourceRuntimeReadAvailability::Available, $runtime->diagnostics()->availability);
    }

    private function runtime(): DeterministicSearchOwnerSourceRuntimeReadV1
    {
        return new DeterministicSearchOwnerSourceRuntimeReadV1($this->source(), new DeterministicSearchOwnerSourceRuntimeReadPolicy);
    }

    private function source(): PostgreSqlSearchOwnerSource
    {
        return new PostgreSqlSearchOwnerSource($this->connection, new SearchOwnerSourceMapper);
    }

    private function observedAt(): SearchObservedAt
    {
        return new SearchObservedAt(new DateTimeImmutable('2026-08-02T00:00:00Z'));
    }

    private function migrate(): void
    {
        $this->connection->exec((string) file_get_contents(dirname(__DIR__, 3).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/075_search_owner_source.sql'));
    }
}
