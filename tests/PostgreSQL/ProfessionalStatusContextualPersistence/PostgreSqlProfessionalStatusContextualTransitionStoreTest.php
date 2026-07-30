<?php

namespace Tests\PostgreSQL\ProfessionalStatusContextualPersistence;

use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\Contract\ProfessionalStatusWorkflowStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualTransitionStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualInspectionStatus;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusContextualTransitionRepository;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusWorkflowRepository;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusContextMapper;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusWorkflowMapper;
use PDO;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Support\ProfessionalStatusContextualTransitionStoreContract;

final class PostgreSqlProfessionalStatusContextualTransitionStoreTest extends ProfessionalStatusContextualTransitionStoreContract
{
    private PDO $connection;

    private ProfessionalStatusWorkflowStore $historical;

    private ProfessionalStatusContextualTransitionStore $store;

    private ProfessionalStatusContextualReplayInspector $inspector;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $workflowMapper = new ProfessionalStatusWorkflowMapper;
        $contextMapper = new ProfessionalStatusContextMapper;
        $this->historical = new PostgreSqlProfessionalStatusWorkflowRepository($this->connection, $workflowMapper);
        $this->store = new PostgreSqlProfessionalStatusContextualTransitionRepository($this->connection, $this->historical, $workflowMapper, $contextMapper);
        $this->inspector = new PostgreSqlProfessionalStatusContextualReplayInspector($this->connection, $workflowMapper, $contextMapper);
    }

    public function test_inspection_restores_exact_append_and_detects_corruption(): void
    {
        $id = $this->contractId();
        $this->seedActive($id);
        $this->store->append($this->contractAppend($id, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

        $found = $this->inspector->inspectLatest($id);
        self::assertSame(ProfessionalStatusContextualInspectionStatus::Found, $found->status);
        self::assertSame(2, $found->snapshot?->version);
        self::assertSame('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', $found->snapshot?->actor->value);

        $this->connection->exec("UPDATE professionals.professional_status_transition_contexts SET context_checksum='".str_repeat('f', 64)."' WHERE professional_id='".$id->value."'");
        self::assertSame(ProfessionalStatusContextualInspectionStatus::Corrupted, $this->inspector->inspectLatest($id)->status);
    }

    public function test_external_transaction_rolls_back_transition_and_context(): void
    {
        $id = ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000029');
        $this->seedActive($id);
        $this->connection->beginTransaction();
        $this->store->append($this->contractAppend($id, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
        $this->connection->rollBack();

        self::assertSame(1, (int) $this->connection->query("SELECT count(*) FROM professionals.professional_status_transitions WHERE professional_id='".$id->value."'")->fetchColumn());
        self::assertSame(0, (int) $this->connection->query("SELECT count(*) FROM professionals.professional_status_transition_contexts WHERE professional_id='".$id->value."'")->fetchColumn());
    }

    public function test_migration_028_and_isolated_rollback_preserve_027(): void
    {
        $down = (string) file_get_contents(dirname(__DIR__, 3).'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/028_professional_status_context.down.sql');
        $up = (string) file_get_contents(dirname(__DIR__, 3).'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/028_professional_status_context.sql');
        $this->connection->exec($down);
        self::assertSame('professionals.professional_status_transitions', $this->connection->query("SELECT to_regclass('professionals.professional_status_transitions')::text")->fetchColumn());
        self::assertNull($this->connection->query("SELECT to_regclass('professionals.professional_status_transition_contexts')")->fetchColumn());
        $this->connection->exec($up);
        self::assertSame('professionals.professional_status_transition_contexts', $this->connection->query("SELECT to_regclass('professionals.professional_status_transition_contexts')::text")->fetchColumn());
    }

    public function test_concurrent_identical_appends_persist_one_transition_and_context(): void
    {
        $results = $this->concurrentResults(['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa']);
        self::assertSame(['already_applied', 'applied'], $results);
        $this->assertSingleConcurrentAppend();
    }

    public function test_concurrent_divergent_contexts_persist_one_context_and_report_divergence(): void
    {
        $results = $this->concurrentResults(['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb']);
        self::assertSame(['applied', 'context_divergence'], $results);
        $this->assertSingleConcurrentAppend();
    }

    /** @param list<string> $actors
     * @return list<string>
     */
    private function concurrentResults(array $actors): array
    {
        $id = ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000030');
        $this->seedActive($id);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'professional-status-context-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ($actors as $index => $actor) {
            $number = $index + 1;
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number, $actor], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start contextual professional status worker.');
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

        return $results;
    }

    private function assertSingleConcurrentAppend(): void
    {
        $id = ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000030');
        self::assertSame(2, (int) $this->connection->query("SELECT count(*) FROM professionals.professional_status_transitions WHERE professional_id='".$id->value."'")->fetchColumn());
        self::assertSame(1, (int) $this->connection->query("SELECT count(*) FROM professionals.professional_status_transition_contexts WHERE professional_id='".$id->value."'")->fetchColumn());
    }

    protected function contextualStore(): ProfessionalStatusContextualTransitionStore
    {
        return $this->store;
    }

    protected function seedActive(ProfessionalStatusId $professionalId): void
    {
        $this->historical->initialize($professionalId);
    }
}
