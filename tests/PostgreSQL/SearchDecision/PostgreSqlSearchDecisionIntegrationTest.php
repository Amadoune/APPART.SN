<?php

namespace Tests\PostgreSQL\SearchDecision;

use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionReadStatus;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionWriteResult;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchDecisionReader;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchDecisionWriter;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\SearchDecisionMapper;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\SearchDecisionFixture;

final class PostgreSqlSearchDecisionIntegrationTest extends TestCase
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

    public function test_corrupt_checksum_and_invalid_payload_are_explicit(): void
    {
        $decision = SearchDecisionFixture::make();
        $this->writer()->store($decision);
        $this->connection->exec("UPDATE search_discovery.public_search_decisions SET payload_checksum='".str_repeat('0', 64)."'");
        self::assertSame(SearchDecisionReadStatus::Corrupted, $this->reader()->readByListing($decision->listingId)->status);

        $this->connection->exec("UPDATE search_discovery.public_search_decisions SET payload='{}',payload_checksum='".hash('sha256', '{}')."'");
        self::assertSame(SearchDecisionReadStatus::Corrupted, $this->reader()->readByListing($decision->listingId)->status);
    }

    public function test_external_transaction_rollback_removes_decision(): void
    {
        $decision = SearchDecisionFixture::make();
        $this->connection->beginTransaction();
        self::assertSame(SearchDecisionWriteResult::Applied, $this->writer()->store($decision));
        $this->connection->rollBack();

        self::assertSame(SearchDecisionReadStatus::Missing, $this->reader()->readByListing($decision->listingId)->status);
    }

    public function test_concurrent_identical_writes_converge_without_double_effect(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-search-decision-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Search decision worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        touch($barrier.'.start');
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            if (proc_close($process) !== 0 || $error !== '') {
                throw new RuntimeException('Search decision worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM search_discovery.public_search_decisions')->fetchColumn());
    }

    private function reader(): PostgreSqlSearchDecisionReader
    {
        return new PostgreSqlSearchDecisionReader($this->connection, $this->mapper);
    }

    private function writer(): PostgreSqlSearchDecisionWriter
    {
        return new PostgreSqlSearchDecisionWriter($this->connection, $this->mapper);
    }
}
