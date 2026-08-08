<?php

namespace Tests\PostgreSQL\AdministrationConsoleOwnerSource;

use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationAuditRevisionState;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationAuditWriteResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationOperatorRevisionState;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationOperatorWriteResult;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationQueueRevisionState;
use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationQueueWriteResult;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;
use Appart\Modules\AdministrationConsole\Infrastructure\Persistence\AdministrationConsoleOwnerSourceMapper;
use Appart\Modules\AdministrationConsole\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrationConsoleOwnerSource;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAdministrationConsoleOwnerSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlAdministrationConsoleOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $migration = dirname(__DIR__, 3).'/src/Modules/AdministrationConsole/Infrastructure/Persistence/PostgreSql/Migrations/082_administration_console_owner_source.sql';
        $this->connection->exec((string) file_get_contents($migration));
        $this->connection->exec('TRUNCATE administration_console.owner_current_index, administration_console.owner_revision_journal');
        $this->source = new PostgreSqlAdministrationConsoleOwnerSource($this->connection, new AdministrationConsoleOwnerSourceMapper);
    }

    public function test_three_streams_are_independent_temporal_and_idempotent(): void
    {
        $key = new AdministrationSubjectKey('console:42');
        $effective = new DateTimeImmutable('2026-08-03T10:00:00Z');
        $recorded = new DateTimeImmutable('2026-08-03T10:00:01Z');
        $operator = new AdministrationOperatorRevisionState($key, 1, AdministrationOperatorStatusV1::Available, $effective, $recorded);
        $queue = new AdministrationQueueRevisionState($key, 1, AdministrationQueueStatusV1::Ready, $effective, $recorded);
        $audit = new AdministrationAuditRevisionState($key, 1, AdministrationAuditStatusV1::Available, $effective, $recorded);
        self::assertSame(AdministrationOperatorWriteResult::Applied, $this->source->appendOperator($operator));
        self::assertSame(AdministrationOperatorWriteResult::AlreadyApplied, $this->source->appendOperator($operator));
        self::assertSame(AdministrationQueueWriteResult::Applied, $this->source->appendQueue($queue));
        self::assertSame(AdministrationAuditWriteResult::Applied, $this->source->appendAudit($audit));
        $second = new AdministrationOperatorRevisionState($key, 2, AdministrationOperatorStatusV1::Unavailable, new DateTimeImmutable('2026-08-03T12:00:00Z'), new DateTimeImmutable('2026-08-03T12:00:01Z'));
        $divergent = new AdministrationOperatorRevisionState($key, 2, AdministrationOperatorStatusV1::Available, new DateTimeImmutable('2026-08-03T12:00:00Z'), new DateTimeImmutable('2026-08-03T12:00:01Z'));
        self::assertSame(AdministrationOperatorWriteResult::Applied, $this->source->appendOperator($second));
        self::assertSame(AdministrationOperatorWriteResult::DivergentRevision, $this->source->appendOperator($divergent));
        self::assertSame(AdministrationOperatorStatusV1::Available, $this->source->readOperator($key, new AdministrationObservedAt(new DateTimeImmutable('2026-08-03T11:00:00Z')))->status);
        self::assertSame(AdministrationOperatorStatusV1::Unavailable, $this->source->readOperator($key, new AdministrationObservedAt(new DateTimeImmutable('2026-08-03T13:00:00Z')))->status);
        self::assertSame(AdministrationQueueStatusV1::Ready, $this->source->readQueue($key, new AdministrationObservedAt(new DateTimeImmutable('2026-08-03T13:00:00Z')))->status);
        self::assertSame(AdministrationAuditStatusV1::Available, $this->source->readAudit($key, new AdministrationObservedAt(new DateTimeImmutable('2026-08-03T13:00:00Z')))->status);
        self::assertSame(3, (int) $this->connection->query('SELECT count(*) FROM administration_console.owner_current_index')->fetchColumn());
    }

    public function test_version_conflict_and_external_rollback_are_preserved(): void
    {
        $second = new AdministrationQueueRevisionState('console:rollback', 2, AdministrationQueueStatusV1::Empty, new DateTimeImmutable('2026-08-03T10:00:00Z'), new DateTimeImmutable('2026-08-03T10:00:01Z'));
        self::assertSame(AdministrationQueueWriteResult::VersionConflict, $this->source->appendQueue($second));
        $first = new AdministrationQueueRevisionState('console:rollback', 1, AdministrationQueueStatusV1::Ready, new DateTimeImmutable('2026-08-03T10:00:00Z'), new DateTimeImmutable('2026-08-03T10:00:01Z'));
        $this->connection->beginTransaction();
        self::assertSame(AdministrationQueueWriteResult::Applied, $this->source->appendQueue($first));
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertSame(0, (int) $this->connection->query("SELECT count(*) FROM administration_console.owner_revision_journal WHERE subject_key='console:rollback'")->fetchColumn());
    }
}
