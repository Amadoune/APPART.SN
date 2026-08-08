<?php

namespace Tests\PostgreSQL\NotificationsOwnerSource;

use Appart\Modules\Notifications\Application\OwnerSource\NotificationChannelRevisionState;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationChannelWriteResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationPreferenceRevisionState;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationPreferenceWriteResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationTemplateRevisionState;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationTemplateWriteResult;
use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateStatusV1;
use Appart\Modules\Notifications\Infrastructure\Persistence\NotificationsOwnerSourceMapper;
use Appart\Modules\Notifications\Infrastructure\Persistence\PostgreSql\PostgreSqlNotificationsOwnerSource;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlNotificationsOwnerSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlNotificationsOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->connection->exec((string) file_get_contents(dirname(__DIR__, 3).'/src/Modules/Notifications/Infrastructure/Persistence/PostgreSql/Migrations/079_notifications_owner_source.sql'));
        $this->connection->exec('TRUNCATE notifications.owner_current_index, notifications.owner_revision_journal');
        $this->source = new PostgreSqlNotificationsOwnerSource($this->connection, new NotificationsOwnerSourceMapper);
    }

    public function test_three_streams_are_independent_temporal_and_idempotent(): void
    {
        $key = new NotificationSubjectKey('subject:42');
        $e = new DateTimeImmutable('2026-08-02T10:00:00Z');
        $r = new DateTimeImmutable('2026-08-02T10:00:01Z');
        $p = new NotificationPreferenceRevisionState($key, 1, NotificationPreferenceStatusV1::Enabled, $e, $r);
        $t = new NotificationTemplateRevisionState($key, 1, NotificationTemplateStatusV1::Available, $e, $r);
        $c = new NotificationChannelRevisionState($key, 1, NotificationChannelStatusV1::Allowed, $e, $r);
        self::assertSame(NotificationPreferenceWriteResult::Applied, $this->source->appendPreference($p));
        self::assertSame(NotificationPreferenceWriteResult::AlreadyApplied, $this->source->appendPreference($p));
        self::assertSame(NotificationTemplateWriteResult::Applied, $this->source->appendTemplate($t));
        self::assertSame(NotificationChannelWriteResult::Applied, $this->source->appendChannel($c));
        $second = new NotificationPreferenceRevisionState($key, 2, NotificationPreferenceStatusV1::Disabled, new DateTimeImmutable('2026-08-02T12:00:00Z'), new DateTimeImmutable('2026-08-02T12:00:01Z'));
        $divergent = new NotificationPreferenceRevisionState($key, 2, NotificationPreferenceStatusV1::Enabled, new DateTimeImmutable('2026-08-02T12:00:00Z'), new DateTimeImmutable('2026-08-02T12:00:01Z'));
        self::assertSame(NotificationPreferenceWriteResult::Applied, $this->source->appendPreference($second));
        self::assertSame(NotificationPreferenceWriteResult::DivergentRevision, $this->source->appendPreference($divergent));
        $beforeSecond = new NotificationObservedAt(new DateTimeImmutable('2026-08-02T11:00:00Z'));
        $afterSecond = new NotificationObservedAt(new DateTimeImmutable('2026-08-02T13:00:00Z'));
        self::assertSame(NotificationPreferenceStatusV1::Enabled, $this->source->readPreference($key, $beforeSecond)->status);
        self::assertSame(NotificationPreferenceStatusV1::Disabled, $this->source->readPreference($key, $afterSecond)->status);
        self::assertSame(NotificationTemplateStatusV1::Available, $this->source->readTemplate($key, $afterSecond)->status);
        self::assertSame(NotificationChannelStatusV1::Allowed, $this->source->readChannel($key, $afterSecond)->status);
        self::assertSame(3, (int) $this->connection->query('SELECT count(*) FROM notifications.owner_current_index')->fetchColumn());
    }

    public function test_version_conflict_and_external_rollback_are_preserved(): void
    {
        $state = new NotificationPreferenceRevisionState('subject:rollback', 2, NotificationPreferenceStatusV1::Disabled, new DateTimeImmutable('2026-08-02T10:00:00Z'), new DateTimeImmutable('2026-08-02T10:00:01Z'));
        self::assertSame(NotificationPreferenceWriteResult::VersionConflict, $this->source->appendPreference($state));
        $first = new NotificationPreferenceRevisionState('subject:rollback', 1, NotificationPreferenceStatusV1::Enabled, new DateTimeImmutable('2026-08-02T10:00:00Z'), new DateTimeImmutable('2026-08-02T10:00:01Z'));
        $this->connection->beginTransaction();
        self::assertSame(NotificationPreferenceWriteResult::Applied, $this->source->appendPreference($first));
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertSame(0, (int) $this->connection->query("SELECT count(*) FROM notifications.owner_revision_journal WHERE subject_key='subject:rollback'")->fetchColumn());
    }
}
