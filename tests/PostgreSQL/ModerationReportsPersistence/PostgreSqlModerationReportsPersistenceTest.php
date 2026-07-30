<?php

namespace Tests\PostgreSQL\ModerationReportsPersistence;

use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceState;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceReadStatus;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceRecord;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueClaimResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueItemState;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationCaseStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationDecisionStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationQueueStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlModerationReportsPersistenceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlModerationCaseStore $cases;

    private PostgreSqlModerationDecisionStore $decisions;

    private PostgreSqlModerationQueueStore $queue;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $mapper = new ModerationPersistenceMapper;
        $this->cases = new PostgreSqlModerationCaseStore($this->connection, $mapper);
        $this->decisions = new PostgreSqlModerationDecisionStore($this->connection, $mapper);
        $this->queue = new PostgreSqlModerationQueueStore($this->connection, $mapper);
    }

    #[Test]
    public function migration_round_trip_and_owner_tables_are_complete(): void
    {
        $tables = $this->connection->query(
            "SELECT table_name FROM information_schema.tables WHERE table_schema='moderation_reports' ORDER BY table_name",
        )->fetchAll(PDO::FETCH_COLUMN);

        self::assertSame([
            'atomic_outbox_appends',
            'case_intents',
            'cases',
            'decision_revisions',
            'decision_supersessions',
            'event_deliveries',
            'finding_revisions',
            'listing_handoff_results',
            'outbox_deliveries',
            'outbox_messages',
            'queue_checkpoints',
            'queue_claim_intents',
            'queue_items',
            'report_revisions',
        ], $tables);

        $this->connection->exec((string) file_get_contents(
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/069_moderation_listing_handoff_results.down.sql',
        ));
        $this->connection->exec((string) file_get_contents(
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/067_moderation_event_outbox.down.sql',
        ));
        $this->connection->exec((string) file_get_contents(
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/066_moderation_atomic_outbox_appends.down.sql',
        ));
        $this->connection->exec((string) file_get_contents(
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/065_moderation_event_delivery.down.sql',
        ));
        $this->connection->exec((string) file_get_contents(
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/064_moderation_queue_claim_intents.down.sql',
        ));
        $this->connection->exec((string) file_get_contents(
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/063_moderation_reports.down.sql',
        ));
        self::assertFalse((bool) $this->connection->query(
            "SELECT EXISTS(SELECT 1 FROM information_schema.schemata WHERE schema_name='moderation_reports')",
        )->fetchColumn());
        $this->connection->exec((string) file_get_contents(
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/063_moderation_reports.sql',
        ));
        $this->connection->exec((string) file_get_contents(
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/064_moderation_queue_claim_intents.sql',
        ));
        $this->connection->exec((string) file_get_contents(
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/065_moderation_event_delivery.sql',
        ));
        $this->connection->exec((string) file_get_contents(
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/066_moderation_atomic_outbox_appends.sql',
        ));
        $this->connection->exec((string) file_get_contents(
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/067_moderation_event_outbox.sql',
        ));
        $this->connection->exec((string) file_get_contents(
            dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/069_moderation_listing_handoff_results.sql',
        ));
        self::assertTrue((bool) $this->connection->query(
            "SELECT EXISTS(SELECT 1 FROM information_schema.schemata WHERE schema_name='moderation_reports')",
        )->fetchColumn());
    }

    #[Test]
    public function case_store_is_bijective_append_only_idempotent_and_versioned(): void
    {
        $initial = $this->state(1, 1, 11, 'a', [$this->record(101, ['category' => 'fraud'])]);
        self::assertSame(ModerationPersistenceWriteResult::Applied, $this->cases->save($initial, 0));
        self::assertSame(ModerationPersistenceWriteResult::AlreadyApplied, $this->cases->save($initial, 0));
        self::assertSame(
            ModerationPersistenceWriteResult::DivergentIntent,
            $this->cases->save($this->state(1, 1, 11, 'b', [$this->record(101, ['category' => 'spam'])]), 0),
        );
        self::assertSame(
            ModerationPersistenceWriteResult::VersionConflict,
            $this->cases->save($this->state(1, 2, 12, 'c', [$this->record(101, ['category' => 'fraud'])]), 0),
        );

        $decision = $this->record(301, ['disposition' => 'Suspend', 'supersededDecisionId' => null]);
        $updated = $this->state(1, 2, 12, 'c', [$this->record(101, ['category' => 'fraud', 'validation' => 'Accepted'])], [$this->record(201, ['code' => 'Confirmed'])], [$decision], $decision->id);
        self::assertSame(ModerationPersistenceWriteResult::Applied, $this->cases->save($updated, 1));

        $read = $this->cases->read($this->id(1));
        self::assertSame(ModerationPersistenceReadStatus::Found, $read->status);
        self::assertEquals($updated, $read->state);
        self::assertSame(2, (int) $this->connection->query('SELECT count(*) FROM moderation_reports.report_revisions')->fetchColumn());
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM moderation_reports.finding_revisions')->fetchColumn());
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM moderation_reports.decision_revisions')->fetchColumn());

        self::assertSame(ModerationPersistenceReadStatus::Found, $this->decisions->readCurrent($this->id(1))->status);
        self::assertSame($decision->id, $this->decisions->read($this->id(1), $decision->id)->decision?->id);
    }

    #[Test]
    public function savepoint_and_transactional_rollback_leave_no_partial_state(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(
            ModerationPersistenceWriteResult::Applied,
            $this->cases->save($this->state(2, 1, 21, 'd', [$this->record(102, ['category' => 'fraud'])]), 0),
        );
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();

        self::assertSame(ModerationPersistenceReadStatus::Missing, $this->cases->read($this->id(2))->status);
        self::assertSame(0, (int) $this->connection->query('SELECT count(*) FROM moderation_reports.case_intents')->fetchColumn());
        self::assertSame(0, (int) $this->connection->query('SELECT count(*) FROM moderation_reports.report_revisions')->fetchColumn());
    }

    #[Test]
    public function queue_projection_claim_lease_checkpoint_and_advisory_lock_are_deterministic(): void
    {
        $item = new ModerationQueueItemState(
            $this->id(401),
            $this->id(1),
            90,
            'fraud',
            'Available',
            null,
            null,
            null,
            1,
            $this->time(),
        );
        self::assertSame(ModerationPersistenceWriteResult::Applied, $this->queue->project($item));
        self::assertSame(ModerationPersistenceWriteResult::AlreadyApplied, $this->queue->project($item));
        self::assertSame(ModerationQueueClaimResult::Claimed, $this->queue->claim(
            $item->queueItemId,
            $this->id(402),
            $this->id(403),
            new DateTimeImmutable('2026-07-29T12:05:00+00:00'),
            $this->time(),
        ));
        self::assertSame(ModerationQueueClaimResult::AlreadyClaimed, $this->queue->claim(
            $item->queueItemId,
            $this->id(402),
            $this->id(403),
            new DateTimeImmutable('2026-07-29T12:05:00+00:00'),
            $this->time(),
        ));
        self::assertSame(ModerationQueueClaimResult::LeaseConflict, $this->queue->claim(
            $item->queueItemId,
            $this->id(404),
            $this->id(405),
            new DateTimeImmutable('2026-07-29T12:06:00+00:00'),
            $this->time(),
        ));
        self::assertSame('Claimed', $this->queue->read($item->queueItemId)?->state);

        self::assertSame(ModerationPersistenceWriteResult::Applied, $this->queue->checkpoint('moderation-queue-v1', 1, $this->time()));
        self::assertSame(ModerationPersistenceWriteResult::AlreadyApplied, $this->queue->checkpoint('moderation-queue-v1', 1, $this->time()));
        self::assertSame(ModerationPersistenceWriteResult::VersionConflict, $this->queue->checkpoint('moderation-queue-v1', 0, $this->time()));
    }

    #[Test]
    public function concurrent_updates_are_serialized_by_the_owner_advisory_lock(): void
    {
        self::assertSame(
            ModerationPersistenceWriteResult::Applied,
            $this->cases->save($this->state(9, 1, 91, 'f', [$this->record(109, ['category' => 'initial'])]), 0),
        );

        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'moderation-case-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start moderation persistence worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        if (! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) {
            throw new RuntimeException('Moderation persistence workers did not reach the barrier.');
        }
        touch($barrier.'.start');

        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            self::assertSame('', trim(stream_get_contents($pipes[2])));
            self::assertSame(0, proc_close($process));
        }
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        sort($results);
        self::assertSame(['applied', 'version_conflict'], $results);
        self::assertSame(2, $this->cases->read($this->id(9))->state?->version);
        self::assertSame(2, (int) $this->connection->query(
            "SELECT count(*) FROM moderation_reports.case_intents WHERE case_id='{$this->id(9)}'",
        )->fetchColumn());
    }

    /**
     * @param  list<ModerationPersistenceRecord>  $reports
     * @param  list<ModerationPersistenceRecord>  $findings
     * @param  list<ModerationPersistenceRecord>  $decisions
     */
    private function state(
        int $case,
        int $version,
        int $intent,
        string $checksum,
        array $reports = [],
        array $findings = [],
        array $decisions = [],
        ?string $currentDecisionId = null,
    ): ModerationCasePersistenceState {
        return new ModerationCasePersistenceState(
            $this->id($case),
            'Listing',
            $this->id($case + 500),
            $currentDecisionId === null ? 'Open' : 'Decided',
            $currentDecisionId,
            $version,
            $this->id($intent),
            str_repeat($checksum, 64),
            $this->time(),
            $reports,
            $findings,
            $decisions,
        );
    }

    /** @param array<string, mixed> $payload */
    private function record(int $id, array $payload): ModerationPersistenceRecord
    {
        return new ModerationPersistenceRecord($this->id($id), $payload, $this->time());
    }

    private function time(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-29T12:00:00+00:00');
    }

    private function id(int $suffix): string
    {
        return sprintf('53000000-0000-4000-8000-%012d', $suffix);
    }
}
