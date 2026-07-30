<?php

namespace Tests\PostgreSQL\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Domain\Exception\AdministrativeActionIdentityConflict;
use Appart\Modules\AdministrationAudit\Domain\Exception\ConcurrentAdministrativeActionModification;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\AdministrationAudit\FakeAdministrativeActionRegistryHarness;

final class PostgreSqlAdministrativeActionConcurrencyTest extends TestCase
{
    private PostgreSqlAdministrativeActionRepository $repository;

    private FakeAdministrativeActionRegistryHarness $fixtures;

    protected function setUp(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $this->repository = new PostgreSqlAdministrativeActionRepository($connection, new AdministrativeActionMapper);
        $this->fixtures = new FakeAdministrativeActionRegistryHarness;
    }

    #[DataProvider('concurrencyProvider')]
    public function test_two_independent_connections_have_exactly_one_winner(string $mode, string $loser): void
    {
        if ($mode === 'save') {
            $this->repository->add($this->fixtures->minimalAction());
        }

        $results = $this->runWorkers($mode);
        self::assertCount(1, array_filter($results, static fn (string $result): bool => $result === 'ok'));
        self::assertCount(1, array_filter($results, static fn (string $result): bool => $result === $loser));
        $stored = $this->repository->find($this->fixtures->primaryId());
        self::assertNotNull($stored);
        self::assertSame($mode === 'save' ? 1 : 0, $stored->version());
    }

    public static function concurrencyProvider(): array
    {
        return [
            'concurrent add' => ['add', AdministrativeActionIdentityConflict::class],
            'concurrent save' => ['save', ConcurrentAdministrativeActionModification::class],
        ];
    }

    /** @return list<string> */
    private function runWorkers(string $mode): array
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-pg-concurrency-'.bin2hex(random_bytes(8));
        $worker = __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php';
        $processes = [];
        for ($index = 1; $index <= 2; $index++) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, $worker, $mode, $barrier, (string) $index], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start PostgreSQL concurrency worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        if (! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) {
            throw new RuntimeException('PostgreSQL workers did not reach the concurrency barrier.');
        }
        touch($barrier.'.start');
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            $exit = proc_close($process);
            if ($exit !== 0 || $error !== '') {
                throw new RuntimeException('PostgreSQL concurrency worker failed without exposing connection details.');
            }
        }
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        return $results;
    }
}
