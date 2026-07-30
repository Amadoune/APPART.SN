<?php

namespace Tests\PostgreSQL\RealEstateCatalog;

use Appart\Modules\RealEstateCatalog\Domain\Exception\ConcurrentPropertyModification;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyIdConflict;
use Appart\Modules\RealEstateCatalog\Domain\Exception\PropertyReferenceConflict;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyRepository;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\RealEstateCatalog\FakePropertyRegistryHarness;

final class PostgreSqlPropertyConcurrencyTest extends TestCase
{
    private PostgreSqlPropertyRepository $repository;

    private FakePropertyRegistryHarness $fixtures;

    protected function setUp(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $this->repository = new PostgreSqlPropertyRepository($connection, new PropertyMapper);
        $this->fixtures = new FakePropertyRegistryHarness;
    }

    #[DataProvider('concurrencyProvider')]
    public function test_two_processes_have_one_winner_and_no_partial_property(string $mode, string $loser): void
    {
        if ($mode === 'save') {
            $this->repository->add($this->fixtures->minimalProperty());
        }
        $results = $this->runWorkers($mode);
        self::assertCount(1, array_filter($results, static fn (string $result): bool => $result === 'ok'));
        self::assertCount(1, array_filter($results, static fn (string $result): bool => $result === $loser));
        $connection = PostgreSqlTestEnvironment::connection();
        self::assertSame(1, (int) $connection->query('SELECT count(*) FROM real_estate_catalog.properties')->fetchColumn());
        self::assertSame(1, (int) $connection->query('SELECT count(*) FROM real_estate_catalog.property_reference_reservations')->fetchColumn());
        self::assertSame(1, (int) $connection->query('SELECT count(*) FROM real_estate_catalog.property_addresses')->fetchColumn());
        if ($mode === 'save') {
            $stored = $this->repository->find($this->fixtures->primaryId());
            self::assertNotNull($stored);
            self::assertSame(1, $stored->version());
            self::assertContains($stored->surface()?->squareMeters, [140, 160]);
        }
    }

    public static function concurrencyProvider(): array
    {
        return [
            ['add_id', PropertyIdConflict::class],
            ['add_reference', PropertyReferenceConflict::class],
            ['save', ConcurrentPropertyModification::class],
        ];
    }

    /** @return list<string> */
    private function runWorkers(string $mode): array
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-property-pg-'.bin2hex(random_bytes(8));
        $processes = [];
        for ($index = 1; $index <= 2; $index++) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $mode, $barrier, (string) $index], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Property concurrency worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        if (! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) {
            throw new RuntimeException('Property workers did not reach the barrier.');
        }
        touch($barrier.'.start');
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            $exit = proc_close($process);
            if ($exit !== 0 || $error !== '') {
                throw new RuntimeException('Property concurrency worker failed without exposing connection details.');
            }
        }
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        return $results;
    }
}
