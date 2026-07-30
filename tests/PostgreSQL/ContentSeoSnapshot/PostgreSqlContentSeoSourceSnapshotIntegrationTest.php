<?php

namespace Tests\PostgreSQL\ContentSeoSnapshot;

use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotReadStatus;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotWriteResult;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\ContentSeoSourceSnapshotMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoSourceSnapshotWriter;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\ContentSeoSourceSnapshotFixture;

final class PostgreSqlContentSeoSourceSnapshotIntegrationTest extends TestCase
{
    private PDO $connection;

    private ContentSeoSourceSnapshotMapper $mapper;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->mapper = new ContentSeoSourceSnapshotMapper;
    }

    public function test_corruption_is_explicit(): void
    {
        $snapshot = ContentSeoSourceSnapshotFixture::make();
        $this->writer()->store($snapshot);
        $this->connection->exec("UPDATE content_seo.public_source_snapshots SET payload_checksum='".str_repeat('0', 64)."'");
        self::assertSame(ContentSeoSnapshotReadStatus::Corrupted, $this->reader()->readByListing($snapshot->listingId)->status);
    }

    public function test_external_rollback_removes_snapshot(): void
    {
        $snapshot = ContentSeoSourceSnapshotFixture::make();
        $this->connection->beginTransaction();
        self::assertSame(ContentSeoSnapshotWriteResult::Applied, $this->writer()->store($snapshot));
        $this->connection->rollBack();
        self::assertSame(ContentSeoSnapshotReadStatus::Missing, $this->reader()->readByListing($snapshot->listingId)->status);
    }

    public function test_concurrent_identical_writes_converge_without_double_effect(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-content-seo-snapshot-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Content/SEO snapshot worker.');
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
                throw new RuntimeException('Content/SEO snapshot worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM content_seo.public_source_snapshots')->fetchColumn());
    }

    private function reader(): PostgreSqlContentSeoSourceSnapshotReader
    {
        return new PostgreSqlContentSeoSourceSnapshotReader($this->connection, $this->mapper);
    }

    private function writer(): PostgreSqlContentSeoSourceSnapshotWriter
    {
        return new PostgreSqlContentSeoSourceSnapshotWriter($this->connection, $this->mapper);
    }
}
