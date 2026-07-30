<?php

namespace Tests\PostgreSQL\ReservationLifecycleWorkflow;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationLifecyclePersistenceReadStatus;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationLifecycleWorkflowRepository;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationLifecycleWorkflowMapper;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlReservationLifecycleWorkflowIntegrationTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_checksum_corruption_is_an_explicit_read_result(): void
    {
        $this->repository()->initialize($this->reservationId(), ReservationLifecycleState::Draft);
        $this->connection->exec("UPDATE reservation_lifecycle.reservation_lifecycle_transitions SET transition_checksum='".str_repeat('0', 64)."'");

        self::assertSame(ReservationLifecyclePersistenceReadStatus::Corrupted, $this->repository()->read($this->reservationId())->status);
    }

    public function test_external_transaction_rollback_is_complete(): void
    {
        $this->connection->beginTransaction();
        $this->repository()->initialize($this->reservationId(), ReservationLifecycleState::Draft);
        $this->connection->rollBack();

        self::assertSame(ReservationLifecyclePersistenceReadStatus::Missing, $this->repository()->read($this->reservationId())->status);
    }

    public function test_current_state_read_is_bounded_and_uses_the_dedicated_index(): void
    {
        $this->connection->exec('SET enable_seqscan = off');
        $statement = $this->connection->prepare('EXPLAIN (FORMAT TEXT) SELECT current_state FROM reservation_lifecycle.reservation_lifecycle_transitions WHERE reservation_id=:reservation_id ORDER BY version DESC LIMIT 1');
        $statement->execute(['reservation_id' => $this->reservationId()->value]);
        $plan = implode("\n", $statement->fetchAll(PDO::FETCH_COLUMN));

        self::assertStringContainsString('reservation_lifecycle_current_state_lookup', $plan);
        self::assertStringContainsString('Limit', $plan);
    }

    public function test_migration_and_rollback_are_reversible(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/';
        $down = (string) file_get_contents($root.'019_reservation_lifecycle_workflow.down.sql');
        $up = (string) file_get_contents($root.'019_reservation_lifecycle_workflow.sql');
        $this->connection->exec($down);
        self::assertNull($this->connection->query("SELECT to_regclass('reservation_lifecycle.reservation_lifecycle_transitions')")->fetchColumn());
        $this->connection->exec($up);
        self::assertSame('reservation_lifecycle.reservation_lifecycle_transitions', $this->connection->query("SELECT to_regclass('reservation_lifecycle.reservation_lifecycle_transitions')")->fetchColumn());
    }

    public function test_concurrent_identical_initializations_converge_without_duplicate_effect(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-reservation-lifecycle-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start reservation lifecycle worker.');
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
                throw new RuntimeException('Reservation lifecycle worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_applied', 'applied'], $results);
        self::assertSame(1, (int) $this->connection->query("SELECT count(*) FROM reservation_lifecycle.reservation_lifecycle_transitions WHERE reservation_id='98100000-0000-4000-8000-000000000099'")->fetchColumn());
    }

    private function repository(): PostgreSqlReservationLifecycleWorkflowRepository
    {
        return new PostgreSqlReservationLifecycleWorkflowRepository($this->connection, new ReservationLifecycleWorkflowMapper);
    }

    private function reservationId(): ReservationId
    {
        return ReservationId::fromString('98100000-0000-4000-8000-000000000001');
    }
}
