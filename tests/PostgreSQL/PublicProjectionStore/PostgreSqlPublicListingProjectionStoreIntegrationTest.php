<?php

namespace Tests\PostgreSQL\PublicProjectionStore;

use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionMapper;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionReader;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionWriter;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\PublicListingReadModelFixture;

final class PostgreSqlPublicListingProjectionStoreIntegrationTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->connection->exec("INSERT INTO public_projection.generations (generation_id,state) VALUES ('96000000-0000-4000-8000-000000000001','active')");
    }

    public function test_external_transaction_rollback_leaves_no_projection(): void
    {
        $writer = $this->writer();
        $this->connection->beginTransaction();
        $writer->applyCurrent($this->record());
        $this->connection->rollBack();

        self::assertSame(0, (int) $this->connection->query('SELECT count(*) FROM public_projection.listing_projections')->fetchColumn());
    }

    public function test_database_constraints_reject_second_active_generation(): void
    {
        $this->expectException(\PDOException::class);
        $this->connection->exec("INSERT INTO public_projection.generations (generation_id,state) VALUES ('96000000-0000-4000-8000-000000000002','active')");
    }

    public function test_corrupt_payload_checksum_is_never_read_silently(): void
    {
        $this->writer()->applyCurrent($this->record());
        $this->connection->exec("UPDATE public_projection.listing_projections SET payload_checksum='".str_repeat('0', 64)."'");

        $this->expectException(RuntimeException::class);
        $mapper = new PostgreSqlPublicListingProjectionMapper;
        (new PostgreSqlPublicListingProjectionReader($this->connection, $mapper))->findByCanonicalPath('annonces/appartement-moderne-dakar');
    }

    public function test_two_processes_converge_to_one_projection_without_corruption(): void
    {
        $results = $this->runWorkers();

        sort($results);
        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM public_projection.listing_projections')->fetchColumn());
    }

    private function writer(): PostgreSqlPublicListingProjectionWriter
    {
        $mapper = new PostgreSqlPublicListingProjectionMapper;

        return new PostgreSqlPublicListingProjectionWriter($this->connection, $mapper, new PostgreSqlPublicListingProjectionReader($this->connection, $mapper));
    }

    private function record(): PublicListingProjectionRecord
    {
        $model = PublicListingReadModelFixture::make();

        return PublicListingProjectionRecord::current($model->listingId, 'annonces/appartement-moderne-dakar', $model, new PublicProjectionWatermark(1, 1, 1, 1, 1, 1, 1), PublicProjectionGenerationId::fromString('96000000-0000-4000-8000-000000000001'));
    }

    /** @return list<string> */
    private function runWorkers(): array
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-projection-store-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start projection store concurrency worker.');
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
                throw new RuntimeException('Projection store concurrency worker failed.');
            }
        }
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        return $results;
    }
}
