<?php

namespace Tests\PostgreSQL\NotificationsOutbox;

use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryPayload;
use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryStatus;
use Appart\Modules\Notifications\Application\Delivery\NotificationDeliveryV1;
use Appart\Modules\Notifications\Application\Event\NotificationEventType;
use Appart\Modules\Notifications\Application\Outbox\NotificationOutboxPolicy;
use Appart\Modules\Notifications\Application\Outbox\NotificationOutboxStatus;
use Appart\Modules\Notifications\Infrastructure\Outbox\PostgreSqlNotificationsOutboxRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlNotificationsOutboxRepositoryTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlNotificationsOutboxRepository $repository;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->connection->exec((string) file_get_contents(dirname(__DIR__, 3).'/src/Modules/Notifications/Infrastructure/Persistence/PostgreSql/Migrations/079_notifications_owner_source.sql'));
        $this->connection->exec((string) file_get_contents(dirname(__DIR__, 3).'/src/Modules/Notifications/Infrastructure/Outbox/Migrations/080_notifications_outbox.sql'));
        $this->connection->exec('TRUNCATE notifications.notification_outbox');
        $this->repository = new PostgreSqlNotificationsOutboxRepository($this->connection, new NotificationOutboxPolicy);
    }

    public function test_append_is_idempotent_and_pending_is_ordered(): void
    {
        $delivery = $this->delivery(NotificationEventType::PreferenceObserved, NotificationDeliveryStatus::Enabled);
        self::assertSame(NotificationOutboxStatus::Applied, $this->repository->append($delivery)->status);
        self::assertSame(NotificationOutboxStatus::AlreadyApplied, $this->repository->append($delivery)->status);
        $pending = $this->repository->pending(10);
        self::assertCount(1, $pending);
        self::assertSame($delivery->payload->canonical(), $pending[0]->delivery->payload->canonical());
    }

    public function test_divergence_retry_bound_and_external_rollback_are_preserved(): void
    {
        $delivery = $this->delivery(NotificationEventType::ChannelObserved, NotificationDeliveryStatus::Blocked);
        $entry = $this->repository->append($delivery);
        $statement = $this->connection->prepare('UPDATE notifications.notification_outbox SET message_checksum=:checksum WHERE message_id=:id');
        $statement->execute(['checksum' => str_repeat('0', 64), 'id' => $entry->messageId]);
        self::assertSame(NotificationOutboxStatus::DivergentMessage, $this->repository->append($delivery)->status);
        $this->connection->exec('UPDATE notifications.notification_outbox SET retry_count=10');
        self::assertSame([], $this->repository->pending(10));
        $second = $this->delivery(NotificationEventType::TemplateObserved, NotificationDeliveryStatus::Available);
        $this->connection->beginTransaction();
        self::assertSame(NotificationOutboxStatus::Applied, $this->repository->append($second)->status);
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        $query = $this->connection->prepare('SELECT count(*) FROM notifications.notification_outbox WHERE message_id=:id');
        $query->execute(['id' => (new NotificationOutboxPolicy)->messageId($second)]);
        self::assertSame(0, (int) $query->fetchColumn());
    }

    private function delivery(NotificationEventType $type, NotificationDeliveryStatus $status): NotificationDeliveryV1
    {
        return new NotificationDeliveryV1(new NotificationDeliveryPayload($type, $status, '2026-08-02T12:00:00.123456Z'));
    }
}
