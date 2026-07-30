<?php

namespace Tests\PostgreSQL\ListingLifecycle;

use Appart\Modules\ListingLifecycle\Domain\Exception\ConcurrentListingModification;
use Appart\Modules\ListingLifecycle\Domain\Exception\ListingIdConflict;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\ListingLifecycle\FakeListingRegistryHarness;

final class PostgreSqlListingConcurrencyTest extends TestCase
{
    private PostgreSqlListingRepository $repository;

    private FakeListingRegistryHarness $fixtures;

    protected function setUp(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $this->repository = new PostgreSqlListingRepository($connection, new ListingMapper);
        $this->fixtures = new FakeListingRegistryHarness;
    }

    #[DataProvider('concurrencyProvider')]
    public function test_two_processes_have_exactly_one_winner_and_no_partial_revision(string $mode, string $loser): void
    {
        if ($mode === 'save') {
            $this->repository->add($this->fixtures->minimalListing());
        }
        $results = $this->runWorkers($mode);
        self::assertCount(1, array_filter($results, static fn (string $result): bool => $result === 'ok'));
        self::assertCount(1, array_filter($results, static fn (string $result): bool => $result === $loser));
        $stored = $this->repository->find($this->fixtures->primaryId());
        self::assertNotNull($stored);
        self::assertSame($mode === 'save' ? 1 : 0, $stored->version());
        self::assertCount($mode === 'save' ? 2 : 1, $stored->revisions());
    }

    public static function concurrencyProvider(): array
    {
        return [['add', ListingIdConflict::class], ['save', ConcurrentListingModification::class]];
    }

    /** @return list<string> */
    private function runWorkers(string $mode): array
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-listing-pg-'.bin2hex(random_bytes(8));
        $processes = [];
        for ($index = 1; $index <= 2; $index++) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $mode, $barrier, (string) $index], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Listing concurrency worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        if (! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) {
            throw new RuntimeException('Listing workers did not reach the barrier.');
        }
        touch($barrier.'.start');
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            $exit = proc_close($process);
            if ($exit !== 0 || $error !== '') {
                throw new RuntimeException('Listing concurrency worker failed without exposing connection details.');
            }
        }
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        return $results;
    }
}
