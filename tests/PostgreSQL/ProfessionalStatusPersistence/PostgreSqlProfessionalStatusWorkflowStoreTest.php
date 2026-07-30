<?php

namespace Tests\PostgreSQL\ProfessionalStatusPersistence;

use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\Contract\ProfessionalStatusWorkflowStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusPersistenceReadStatus;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusWorkflowRepository;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusWorkflowMapper;
use PDO;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\ProfessionalStatusWorkflowStoreContract;

final class PostgreSqlProfessionalStatusWorkflowStoreTest extends ProfessionalStatusWorkflowStoreContract
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->pdo);
        parent::setUp();
    }

    protected function store(): ProfessionalStatusWorkflowStore
    {
        return new PostgreSqlProfessionalStatusWorkflowRepository($this->pdo, new ProfessionalStatusWorkflowMapper);
    }

    protected function resetStore(): void
    {
        PostgreSqlTestEnvironment::reset($this->pdo);
    }

    public function test_corrupted_row_is_reported_without_exception(): void
    {
        $this->store()->initialize($this->id());
        $this->pdo->exec("UPDATE professionals.professional_status_transitions SET transition_checksum='".str_repeat('0', 64)."'");
        self::assertSame(ProfessionalStatusPersistenceReadStatus::Corrupted, $this->store()->read($this->id())->status);
    }

    public function test_external_transaction_rollback_removes_all_writes(): void
    {
        $this->pdo->beginTransaction();
        $this->store()->initialize($this->id());
        $this->store()->append($this->id(), $this->suspend(), 2);
        $this->pdo->rollBack();
        self::assertSame(0, (int) $this->pdo->query('SELECT count(*) FROM professionals.professional_status_transitions')->fetchColumn());
    }

    public function test_migration_and_rollback_are_isolated(): void
    {
        $down = file_get_contents(dirname(__DIR__, 3).'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/027_professional_status_workflow.down.sql');
        self::assertIsString($down);
        $this->pdo->exec($down);
        self::assertNull($this->pdo->query("SELECT to_regclass('professionals.professional_status_transitions')")->fetchColumn());
        $up = file_get_contents(dirname(__DIR__, 3).'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/027_professional_status_workflow.sql');
        self::assertIsString($up);
        $this->pdo->exec($up);
        self::assertSame('professionals.professional_status_transitions', $this->pdo->query("SELECT to_regclass('professionals.professional_status_transitions')::text")->fetchColumn());
    }

    public function test_concurrent_identical_appends_converge_without_duplicate(): void
    {
        $id = ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000020');
        $this->store()->initialize($id);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'professional-status-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start professional status worker.');
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
                throw new RuntimeException($error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(2, (int) $this->pdo->query("SELECT count(*) FROM professionals.professional_status_transitions WHERE professional_id='a4500000-0000-4000-8000-000000000020'")->fetchColumn());
    }
}
