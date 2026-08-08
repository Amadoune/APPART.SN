<?php

namespace Tests\PostgreSQL\SearchQueryResolutionOutbox;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery\SearchQueryResolutionDeliveryPayload;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery\SearchQueryResolutionDeliveryStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionDelivery\SearchQueryResolutionDeliveryV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOutbox\SearchQueryResolutionOutboxPolicy;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\SearchQueryResolutionOutboxRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlSearchQueryResolutionOutboxTest extends TestCase
{
    private PDO $connection;

    private SearchQueryResolutionOutboxRepository $repository;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $migration = dirname(__DIR__, 3).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/077_search_query_resolution_outbox.sql';
        $this->connection->exec((string) file_get_contents($migration));
        $this->connection->exec('TRUNCATE search_discovery.search_query_resolution_outbox');
        $this->repository = new SearchQueryResolutionOutboxRepository($this->connection, new SearchQueryResolutionOutboxPolicy);
    }

    public function test_append_is_idempotent_and_pending_is_deterministic(): void
    {
        $delivery = new SearchQueryResolutionDeliveryV1(new SearchQueryResolutionDeliveryPayload(SearchQueryResolutionDeliveryStatus::Found, '2026-08-02T16:00:00.123456Z'));
        self::assertFalse($this->repository->append($delivery)->alreadyApplied);
        self::assertTrue($this->repository->append($delivery)->alreadyApplied);
        self::assertCount(1, $this->repository->pending(10));
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM search_discovery.search_query_resolution_outbox')->fetchColumn());
    }
}
