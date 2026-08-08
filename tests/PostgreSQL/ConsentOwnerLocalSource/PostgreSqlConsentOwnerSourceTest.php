<?php

namespace Tests\PostgreSQL\ConsentOwnerLocalSource;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionDecision;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionReadStatus;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionState;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionWriteResult;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\ConsentOwnerSourceMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlConsentOwnerSource;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\ContactsLeads\ConsentOwnerSource\ConsentOwnerSourceMapperTest;

final class PostgreSqlConsentOwnerSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlConsentOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->source = new PostgreSqlConsentOwnerSource($this->connection, new ConsentOwnerSourceMapper);
    }

    #[Test]
    public function append_replay_divergence_versioning_and_temporal_reads_are_deterministic(): void
    {
        $granted = $this->state(1, ConsentRevisionDecision::Granted, '2026-07-31T08:00:00Z');
        self::assertSame(ConsentRevisionWriteResult::Applied, $this->source->append($granted));
        self::assertSame(ConsentRevisionWriteResult::AlreadyApplied, $this->source->append($granted));
        self::assertSame(
            ConsentRevisionWriteResult::DivergentRevision,
            $this->source->append($this->state(1, ConsentRevisionDecision::Denied, '2026-07-31T08:00:00Z')),
        );
        self::assertSame(
            ConsentRevisionWriteResult::VersionConflict,
            $this->source->append($this->state(3, ConsentRevisionDecision::Withdrawn, '2026-07-31T10:00:00Z')),
        );

        $withdrawn = $this->state(2, ConsentRevisionDecision::Withdrawn, '2026-07-31T09:00:00Z');
        self::assertSame(ConsentRevisionWriteResult::Applied, $this->source->append($withdrawn));
        self::assertSame(
            ConsentRevisionDecision::Granted,
            $this->source->at(ConsentOwnerSourceMapperTest::intent(), new DateTimeImmutable('2026-07-31T08:30:00Z'))->revision?->decision,
        );
        self::assertSame(
            ConsentRevisionDecision::Withdrawn,
            $this->source->at(ConsentOwnerSourceMapperTest::intent(), new DateTimeImmutable('2026-07-31T09:30:00Z'))->revision?->decision,
        );
        self::assertSame(
            ConsentRevisionReadStatus::Missing,
            $this->source->at(ConsentOwnerSourceMapperTest::intent(), new DateTimeImmutable('2026-07-31T07:59:59Z'))->status,
        );
        self::assertCount(2, $this->source->history(ConsentOwnerSourceMapperTest::intent()));
    }

    #[Test]
    public function rollback_savepoint_and_corruption_are_fail_closed(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(
            ConsentRevisionWriteResult::Applied,
            $this->source->append($this->state(1, ConsentRevisionDecision::Undecided, '2026-07-31T08:00:00Z')),
        );
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertSame(
            ConsentRevisionReadStatus::Missing,
            $this->source->at(ConsentOwnerSourceMapperTest::intent(), new DateTimeImmutable('2026-07-31T09:00:00Z'))->status,
        );

        self::assertSame(
            ConsentRevisionWriteResult::Applied,
            $this->source->append($this->state(1, ConsentRevisionDecision::Granted, '2026-07-31T08:00:00Z')),
        );
        $this->connection->exec(
            "UPDATE contacts_leads.consent_decision_revisions
             SET revision_checksum=repeat('0',64)",
        );
        self::assertSame(
            ConsentRevisionReadStatus::Corrupted,
            $this->source->at(ConsentOwnerSourceMapperTest::intent(), new DateTimeImmutable('2026-07-31T09:00:00Z'))->status,
        );
    }

    #[Test]
    public function migration_rollback_and_remigration_preserve_historical_contacts_leads(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'072_consent_owner_local_source.down.sql'));
        self::assertSame(
            0,
            (int) $this->connection->query(
                "SELECT count(*) FROM information_schema.tables
                 WHERE table_schema='contacts_leads' AND table_name='consent_decision_revisions'",
            )->fetchColumn(),
        );
        self::assertSame(
            1,
            (int) $this->connection->query(
                "SELECT count(*) FROM information_schema.tables
                 WHERE table_schema='contacts_leads' AND table_name='lead_lifecycle_transitions'",
            )->fetchColumn(),
        );
        self::assertSame(
            ConsentRevisionReadStatus::DependencyUnavailable,
            $this->source->at(ConsentOwnerSourceMapperTest::intent(), new DateTimeImmutable('2026-07-31T09:00:00Z'))->status,
        );
        $this->connection->exec((string) file_get_contents($root.'072_consent_owner_local_source.sql'));
        self::assertSame(
            1,
            (int) $this->connection->query(
                "SELECT count(*) FROM pg_indexes
                 WHERE schemaname='contacts_leads' AND indexname='consent_decision_temporal_read'",
            )->fetchColumn(),
        );
    }

    #[Test]
    public function concurrent_identical_appends_converge_without_an_advisory_lock(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'consent-source-'.bin2hex(random_bytes(8));
        $processes = [];
        for ($worker = 1; $worker <= 2; $worker++) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start consent source concurrency worker.');
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
        self::assertSame(
            1,
            (int) $this->connection->query('SELECT count(*) FROM contacts_leads.consent_decision_revisions')->fetchColumn(),
        );
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
    }

    private function state(int $revision, ConsentRevisionDecision $decision, string $effectiveAt): ConsentRevisionState
    {
        $effective = new DateTimeImmutable($effectiveAt);

        return new ConsentRevisionState(
            ConsentOwnerSourceMapperTest::intent(),
            $revision,
            $decision,
            $effective,
            $effective->modify('+1 minute'),
            'contact-v1',
        );
    }
}
