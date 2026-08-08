<?php

namespace Tests\PostgreSQL\ReservationAvailabilityOwnerSource;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityReadResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityReadStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionDecision;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityWriteResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityObservedAt;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationAvailabilityOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\ReservationAvailabilityOwnerSource\ReservationAvailabilityOwnerSourceMapperTest;

final class PostgreSqlReservationAvailabilityOwnerSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlReservationAvailabilityOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->connection->exec((string) file_get_contents($this->migrationRoot().'074_reservation_availability_owner_local_source.sql'));
        $this->connection->exec('TRUNCATE reservation_lifecycle.availability_intent_revisions');
        $this->source = new PostgreSqlReservationAvailabilityOwnerSource($this->connection, new ReservationAvailabilityOwnerSourceMapper);
    }

    #[Test]
    public function append_replay_divergence_versioning_temporal_read_and_conflicts_are_deterministic(): void
    {
        $proposed = ReservationAvailabilityOwnerSourceMapperTest::state(1, ReservationAvailabilityRevisionDecision::Proposed, '08:00');
        self::assertSame(ReservationAvailabilityWriteResult::Applied, $this->source->append($proposed));
        self::assertSame(ReservationAvailabilityWriteResult::AlreadyApplied, $this->source->append($proposed));
        self::assertSame(
            ReservationAvailabilityWriteResult::DivergentRevision,
            $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(1, ReservationAvailabilityRevisionDecision::Held, '08:00')),
        );
        self::assertSame(
            ReservationAvailabilityWriteResult::VersionConflict,
            $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(3, ReservationAvailabilityRevisionDecision::Committed, '10:00')),
        );
        self::assertSame(
            ReservationAvailabilityWriteResult::Applied,
            $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(2, ReservationAvailabilityRevisionDecision::Held, '09:00')),
        );

        self::assertSame(ReservationAvailabilityReadStatus::Missing, $this->read(1, '08:00')->status);
        self::assertSame(ReservationAvailabilityReadStatus::Available, $this->read(1, '09:30')->status);

        self::assertSame(
            ReservationAvailabilityWriteResult::Applied,
            $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(1, ReservationAvailabilityRevisionDecision::Proposed, '09:01', 2)),
        );
        self::assertSame(ReservationAvailabilityReadStatus::Conflicting, $this->read(2, '09:30')->status);
        self::assertSame(
            ReservationAvailabilityWriteResult::AvailabilityConflict,
            $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(2, ReservationAvailabilityRevisionDecision::Held, '09:10', 2)),
        );

        self::assertSame(
            ReservationAvailabilityWriteResult::Applied,
            $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(3, ReservationAvailabilityRevisionDecision::Released, '10:00')),
        );
        self::assertSame(
            ReservationAvailabilityWriteResult::Applied,
            $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(2, ReservationAvailabilityRevisionDecision::Held, '10:10', 2)),
        );
        self::assertCount(3, $this->source->history(ReservationAvailabilityOwnerSourceMapperTest::intent()));
    }

    #[Test]
    public function owner_transaction_savepoint_rollback_and_corruption_are_fail_closed(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(
            ReservationAvailabilityWriteResult::Applied,
            $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(1, ReservationAvailabilityRevisionDecision::Proposed, '08:00')),
        );
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertSame(ReservationAvailabilityReadStatus::Missing, $this->read(1, '09:00')->status);

        self::assertSame(
            ReservationAvailabilityWriteResult::Applied,
            $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(1, ReservationAvailabilityRevisionDecision::Proposed, '08:00')),
        );
        $this->connection->exec("UPDATE reservation_lifecycle.availability_intent_revisions SET revision_checksum=repeat('0',64)");
        self::assertSame(ReservationAvailabilityReadStatus::Corrupted, $this->read(1, '09:00')->status);
    }

    #[Test]
    public function migration_rollback_remigration_and_indexes_are_complete(): void
    {
        $root = $this->migrationRoot();
        $this->connection->exec((string) file_get_contents($root.'074_reservation_availability_owner_local_source.down.sql'));
        self::assertSame(ReservationAvailabilityReadStatus::DependencyUnavailable, $this->read(1, '09:00')->status);
        $this->connection->exec((string) file_get_contents($root.'074_reservation_availability_owner_local_source.sql'));
        self::assertSame(2, (int) $this->connection->query("SELECT count(*) FROM pg_indexes WHERE schemaname='reservation_lifecycle' AND indexname IN ('reservation_availability_subject_temporal_read','reservation_availability_overlap_lookup')")->fetchColumn());
    }

    #[Test]
    public function concurrent_identical_appends_converge_to_one_applied_and_one_already_applied(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'reservation-availability-source-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.'/concurrency-worker.php', $barrier, (string) $worker], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start reservation availability concurrency worker.');
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
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM reservation_lifecycle.availability_intent_revisions')->fetchColumn());
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
    }

    #[Test]
    public function concurrent_overlapping_holds_allow_exactly_one_acquisition(): void
    {
        foreach ([1, 2] as $intentSuffix) {
            self::assertSame(
                ReservationAvailabilityWriteResult::Applied,
                $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(1, ReservationAvailabilityRevisionDecision::Proposed, '08:00', $intentSuffix)),
            );
        }

        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'reservation-availability-hold-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.'/concurrency-worker.php', $barrier, (string) $worker, 'hold'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start reservation availability hold worker.');
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
        self::assertSame(['applied', 'availability_conflict'], $results);
        self::assertSame(3, (int) $this->connection->query('SELECT count(*) FROM reservation_lifecycle.availability_intent_revisions')->fetchColumn());
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
    }

    private function read(int $intentSuffix, string $time): ReservationAvailabilityReadResult
    {
        return $this->source->read(
            ReservationAvailabilityOwnerSourceMapperTest::intent($intentSuffix),
            ReservationAvailabilityOwnerSourceMapperTest::subject(),
            ReservationAvailabilityOwnerSourceMapperTest::window(),
            new ReservationAvailabilityObservedAt(new DateTimeImmutable('2026-08-01T'.$time.':00Z')),
        );
    }

    private function migrationRoot(): string
    {
        return dirname(__DIR__, 3).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/';
    }
}
