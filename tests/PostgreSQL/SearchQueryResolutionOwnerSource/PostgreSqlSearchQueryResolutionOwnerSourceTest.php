<?php

namespace Tests\PostgreSQL\SearchQueryResolutionOwnerSource;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionReadResult;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionReadStatus;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionRevisionDecision;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionOwnerSource\SearchQueryResolutionWriteResult;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchQueryResolutionOwnerSource;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchQueryResolutionOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\SearchDiscovery\SearchQueryResolutionOwnerSource\SearchQueryResolutionOwnerSourceMapperTest;

final class PostgreSqlSearchQueryResolutionOwnerSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlSearchQueryResolutionOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->connection->exec((string) file_get_contents($this->root().'076_search_query_resolution_owner_source.sql'));
        $this->connection->exec('TRUNCATE search_discovery.search_query_resolution_current_index, search_discovery.search_query_resolution_revision_journal');
        $this->source = new PostgreSqlSearchQueryResolutionOwnerSource($this->connection, new SearchQueryResolutionOwnerSourceMapper);
    }

    #[Test]
    public function journal_is_authoritative_idempotent_and_temporal(): void
    {
        $first = SearchQueryResolutionOwnerSourceMapperTest::state();
        self::assertSame(SearchQueryResolutionWriteResult::Applied, $this->source->append($first));
        self::assertSame(SearchQueryResolutionWriteResult::AlreadyApplied, $this->source->append($first));
        self::assertSame(SearchQueryResolutionWriteResult::DivergentRevision, $this->source->append(SearchQueryResolutionOwnerSourceMapperTest::state(1, SearchQueryResolutionRevisionDecision::Empty)));
        self::assertSame(SearchQueryResolutionWriteResult::VersionConflict, $this->source->append(SearchQueryResolutionOwnerSourceMapperTest::state(3, SearchQueryResolutionRevisionDecision::Empty, '10:00:00')));
        self::assertSame(SearchQueryResolutionWriteResult::Applied, $this->source->append(SearchQueryResolutionOwnerSourceMapperTest::state(2, SearchQueryResolutionRevisionDecision::Empty, '09:00:00')));
        self::assertSame(SearchQueryResolutionReadStatus::Missing, $this->read('07:59:59')->status);
        self::assertSame(SearchQueryResolutionReadStatus::Found, $this->read('08:30:00')->status);
        self::assertSame(SearchQueryResolutionReadStatus::Empty, $this->read('09:30:00')->status);
        self::assertSame(2, (int) $this->connection->query('SELECT count(*) FROM search_discovery.search_query_resolution_revision_journal')->fetchColumn());
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM search_discovery.search_query_resolution_current_index')->fetchColumn());
    }

    #[Test]
    public function savepoints_corruption_and_rollback_are_fail_closed(): void
    {
        self::assertSame(SearchQueryResolutionWriteResult::Applied, $this->source->append(SearchQueryResolutionOwnerSourceMapperTest::state()));
        $this->connection->beginTransaction();
        self::assertSame(SearchQueryResolutionWriteResult::Applied, $this->source->append(SearchQueryResolutionOwnerSourceMapperTest::state(2, SearchQueryResolutionRevisionDecision::Empty, '09:00:00')));
        $this->connection->rollBack();
        self::assertCount(1, $this->source->history(SearchQueryResolutionOwnerSourceMapperTest::query()));
        $this->connection->exec("UPDATE search_discovery.search_query_resolution_revision_journal SET revision_checksum=repeat('0',64)");
        self::assertSame(SearchQueryResolutionReadStatus::Corrupted, $this->read('10:00:00')->status);

        $this->connection->exec((string) file_get_contents($this->root().'076_search_query_resolution_owner_source.down.sql'));
        self::assertSame(SearchQueryResolutionReadStatus::DependencyUnavailable, $this->read('10:00:00')->status);
    }

    #[Test]
    public function concurrent_identical_appends_have_one_winner_and_converge(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'search-query-resolution-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.'/concurrency-worker.php', $barrier, (string) $worker], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Search query resolution concurrency worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        self::assertFileExists($barrier.'.ready.1');
        self::assertFileExists($barrier.'.ready.2');
        touch($barrier.'.start');
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            self::assertSame(0, proc_close($process), $error);
        }
        sort($results);
        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM search_discovery.search_query_resolution_revision_journal')->fetchColumn());
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM search_discovery.search_query_resolution_current_index')->fetchColumn());
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
    }

    private function read(string $time): SearchQueryResolutionReadResult
    {
        return $this->source->read(SearchQueryResolutionOwnerSourceMapperTest::query(), new SearchQueryResolutionObservedAt(new DateTimeImmutable('2026-08-01T'.$time.'.500000Z')));
    }

    private function root(): string
    {
        return dirname(__DIR__, 3).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/';
    }
}
