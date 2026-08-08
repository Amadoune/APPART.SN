<?php

namespace Tests\PostgreSQL\AntiAbuseOwnerSource;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionDecision;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionReadStatus;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionState;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionWriteResult;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\AntiAbuseOwnerSourceMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlAntiAbuseOwnerSource;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\ContactsLeads\AntiAbuseOwnerSource\AntiAbuseOwnerSourceMapperTest;

final class PostgreSqlAntiAbuseOwnerSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlAntiAbuseOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->connection->exec((string) file_get_contents($this->migrationRoot().'073_anti_abuse_owner_local_source.sql'));
        $this->connection->exec('TRUNCATE contacts_leads.anti_abuse_decision_revisions');
        $this->source = new PostgreSqlAntiAbuseOwnerSource($this->connection, new AntiAbuseOwnerSourceMapper);
    }

    public function test_append_replay_divergence_versioning_and_temporal_reads(): void
    {
        $allowed = $this->state(1, AntiAbuseRevisionDecision::Allowed, '08:00');
        self::assertSame(AntiAbuseRevisionWriteResult::Applied, $this->source->append($allowed));
        self::assertSame(AntiAbuseRevisionWriteResult::AlreadyApplied, $this->source->append($allowed));
        self::assertSame(AntiAbuseRevisionWriteResult::DivergentRevision, $this->source->append($this->state(1, AntiAbuseRevisionDecision::Blocked, '08:00')));
        self::assertSame(AntiAbuseRevisionWriteResult::VersionConflict, $this->source->append($this->state(3, AntiAbuseRevisionDecision::Blocked, '10:00')));
        self::assertSame(AntiAbuseRevisionWriteResult::Applied, $this->source->append($this->state(2, AntiAbuseRevisionDecision::Blocked, '09:00')));

        self::assertSame(AntiAbuseRevisionReadStatus::Missing, $this->read('07:59')->status);
        self::assertSame(AntiAbuseRevisionDecision::Allowed, $this->read('08:30')->revision?->decision);
        self::assertSame(AntiAbuseRevisionDecision::Blocked, $this->read('09:30')->revision?->decision);
        self::assertCount(2, $this->source->history(AntiAbuseOwnerSourceMapperTest::intent()));
    }

    public function test_rollback_savepoint_and_corruption_are_fail_closed(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(AntiAbuseRevisionWriteResult::Applied, $this->source->append($this->state(1, AntiAbuseRevisionDecision::Allowed, '08:00')));
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertSame(AntiAbuseRevisionReadStatus::Missing, $this->read('09:00')->status);

        self::assertSame(AntiAbuseRevisionWriteResult::Applied, $this->source->append($this->state(1, AntiAbuseRevisionDecision::Allowed, '08:00')));
        $this->connection->exec("UPDATE contacts_leads.anti_abuse_decision_revisions SET revision_checksum=repeat('0',64)");
        self::assertSame(AntiAbuseRevisionReadStatus::Corrupted, $this->read('09:00')->status);
    }

    public function test_migration_rollback_remigration_and_index_are_complete(): void
    {
        $root = $this->migrationRoot();
        $this->connection->exec((string) file_get_contents($root.'073_anti_abuse_owner_local_source.down.sql'));
        self::assertSame(AntiAbuseRevisionReadStatus::DependencyUnavailable, $this->read('09:00')->status);
        $this->connection->exec((string) file_get_contents($root.'073_anti_abuse_owner_local_source.sql'));
        self::assertSame(1, (int) $this->connection->query("SELECT count(*) FROM pg_indexes WHERE schemaname='contacts_leads' AND indexname='anti_abuse_decision_temporal_read'")->fetchColumn());
    }

    public function test_concurrent_identical_appends_converge(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'anti-abuse-source-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.'/concurrency-worker.php', $barrier, (string) $worker], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start anti-abuse concurrency worker.');
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
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM contacts_leads.anti_abuse_decision_revisions')->fetchColumn());
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
    }

    private function read(string $time): AntiAbuseRevisionReadResult
    {
        return $this->source->at(
            AntiAbuseOwnerSourceMapperTest::intent(),
            new LeadIngressAntiAbuseObservedAt(new DateTimeImmutable('2026-07-31T'.$time.':00Z')),
        );
    }

    private function state(int $revision, AntiAbuseRevisionDecision $decision, string $time): AntiAbuseRevisionState
    {
        $effectiveAt = new DateTimeImmutable('2026-07-31T'.$time.':00Z');

        return new AntiAbuseRevisionState(
            AntiAbuseOwnerSourceMapperTest::intent(),
            $revision,
            $decision,
            $effectiveAt,
            $effectiveAt->modify('+1 minute'),
            'anti-abuse-v1',
        );
    }

    private function migrationRoot(): string
    {
        return dirname(__DIR__, 3).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/';
    }
}
