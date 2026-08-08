<?php

namespace Tests\PostgreSQL\SearchOwnerSource;

use Appart\Modules\SearchDiscovery\Application\SearchExperiencePublicRead\SearchObservedAt;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerReadResult;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerReadStatus;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerWriteResult;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchOwnerSource;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\SearchDiscovery\SearchOwnerSource\SearchOwnerSourceMapperTest;

final class PostgreSqlSearchOwnerSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlSearchOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->connection->exec((string) file_get_contents($this->root().'075_search_owner_source.sql'));
        $this->connection->exec('TRUNCATE search_discovery.search_owner_current_index, search_discovery.search_owner_revision_journal');
        $this->source = new PostgreSqlSearchOwnerSource($this->connection, new SearchOwnerSourceMapper);
    }

    #[Test]
    public function journal_is_authoritative_and_current_index_is_derived_atomically(): void
    {
        $first = SearchOwnerSourceMapperTest::state();
        self::assertSame(SearchOwnerWriteResult::Applied, $this->source->append($first));
        self::assertSame(SearchOwnerWriteResult::AlreadyApplied, $this->source->append($first));
        self::assertSame(SearchOwnerWriteResult::DivergentRevision, $this->source->append(SearchOwnerSourceMapperTest::state(1, 501)));
        self::assertSame(SearchOwnerWriteResult::VersionConflict, $this->source->append(SearchOwnerSourceMapperTest::state(3, 503, '10:00:00')));
        self::assertSame(SearchOwnerWriteResult::Applied, $this->source->append(SearchOwnerSourceMapperTest::state(2, 502, '09:00:00')));

        self::assertSame(2, (int) $this->connection->query('SELECT count(*) FROM search_discovery.search_owner_revision_journal')->fetchColumn());
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM search_discovery.search_owner_current_index')->fetchColumn());
        self::assertSame(2, (int) $this->connection->query('SELECT revision FROM search_discovery.search_owner_current_index')->fetchColumn());
    }

    #[Test]
    public function temporal_read_savepoint_and_corruption_are_fail_closed(): void
    {
        self::assertSame(SearchOwnerWriteResult::Applied, $this->source->append(SearchOwnerSourceMapperTest::state()));
        self::assertSame(SearchOwnerReadStatus::Missing, $this->read('07:59:59')->status);
        self::assertSame(SearchOwnerReadStatus::Found, $this->read('08:00:01')->status);

        $this->connection->beginTransaction();
        self::assertSame(SearchOwnerWriteResult::Applied, $this->source->append(SearchOwnerSourceMapperTest::state(2, 502, '09:00:00')));
        $this->connection->rollBack();
        self::assertCount(1, $this->source->history(SearchOwnerSourceMapperTest::document()));

        $this->connection->exec("UPDATE search_discovery.search_owner_revision_journal SET revision_checksum=repeat('0',64)");
        self::assertSame(SearchOwnerReadStatus::Corrupted, $this->read('10:00:00')->status);
    }

    #[Test]
    public function rollback_is_complete_and_dependency_failure_is_closed(): void
    {
        $this->connection->exec((string) file_get_contents($this->root().'075_search_owner_source.down.sql'));
        self::assertSame(SearchOwnerReadStatus::DependencyUnavailable, $this->read('10:00:00')->status);
        self::assertFalse((bool) $this->connection->query("SELECT to_regclass('search_discovery.search_owner_revision_journal')")->fetchColumn());
        self::assertFalse((bool) $this->connection->query("SELECT to_regclass('search_discovery.search_owner_current_index')")->fetchColumn());
    }

    #[Test]
    public function concurrent_identical_appends_have_one_winner_and_converge(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'search-owner-source-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.'/concurrency-worker.php', $barrier, (string) $worker], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Search owner concurrency worker.');
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
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM search_discovery.search_owner_revision_journal')->fetchColumn());
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM search_discovery.search_owner_current_index')->fetchColumn());
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
    }

    private function read(string $time): SearchOwnerReadResult
    {
        return $this->source->read(SearchOwnerSourceMapperTest::document(), new SearchObservedAt(new DateTimeImmutable('2026-08-01T'.$time.'.500000Z')));
    }

    private function root(): string
    {
        return dirname(__DIR__, 3).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/';
    }
}
