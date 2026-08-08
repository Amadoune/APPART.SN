<?php

namespace Tests\PostgreSQL\AdministrationConsoleOutbox;

use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryPayload;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryStatus;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationAuditDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryPayload;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryStatus;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationOperatorDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryPayload;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryStatus;
use Appart\Modules\AdministrationConsole\Application\Delivery\AdministrationQueueDeliveryV1;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationAuditEventType;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationOperatorEventType;
use Appart\Modules\AdministrationConsole\Application\Event\AdministrationQueueEventType;
use Appart\Modules\AdministrationConsole\Application\Outbox\AdministrationConsoleOutboxPolicy;
use Appart\Modules\AdministrationConsole\Application\Outbox\AdministrationConsoleOutboxStatus;
use Appart\Modules\AdministrationConsole\Infrastructure\Outbox\PostgreSqlAdministrationConsoleOutboxRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAdministrationConsoleOutboxRepositoryTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlAdministrationConsoleOutboxRepository $repository;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        $root = dirname(__DIR__, 3);
        $this->connection->exec((string) file_get_contents($root.'/src/Modules/AdministrationConsole/Infrastructure/Persistence/PostgreSql/Migrations/082_administration_console_owner_source.sql'));
        $this->connection->exec((string) file_get_contents($root.'/src/Modules/AdministrationConsole/Infrastructure/Outbox/Migrations/083_administration_console_outbox.sql'));
        $this->connection->exec('TRUNCATE administration_console.outbox_messages');
        $this->repository = new PostgreSqlAdministrationConsoleOutboxRepository($this->connection, new AdministrationConsoleOutboxPolicy);
    }

    public function test_append_is_idempotent_and_pending_reconstructs_all_streams_in_order(): void
    {
        $deliveries = [$this->operator(), $this->queue(), $this->audit()];
        foreach ($deliveries as $delivery) {
            self::assertSame(AdministrationConsoleOutboxStatus::Applied, $this->repository->append($delivery)->status);
            self::assertSame(AdministrationConsoleOutboxStatus::AlreadyApplied, $this->repository->append($delivery)->status);
        }

        $pending = $this->repository->pending(10);
        self::assertCount(3, $pending);
        self::assertSame(array_map(static fn ($entry): string => $entry->messageId, $pending), array_values(array_unique(array_map(static fn ($entry): string => $entry->messageId, $pending))));
        foreach ($pending as $entry) {
            self::assertSame('2026-08-03T12:00:00.123456Z', $entry->delivery->payload->observedAt);
        }
    }

    public function test_divergence_retry_bound_and_external_rollback_are_preserved(): void
    {
        $delivery = $this->operator();
        $entry = $this->repository->append($delivery);
        $statement = $this->connection->prepare('UPDATE administration_console.outbox_messages SET message_checksum=:checksum WHERE message_id=:id');
        $statement->execute(['checksum' => str_repeat('0', 64), 'id' => $entry->messageId]);
        self::assertSame(AdministrationConsoleOutboxStatus::DivergentMessage, $this->repository->append($delivery)->status);

        $this->connection->exec('UPDATE administration_console.outbox_messages SET retry_count=10');
        self::assertSame([], $this->repository->pending(10));

        $second = $this->queue();
        $this->connection->beginTransaction();
        self::assertSame(AdministrationConsoleOutboxStatus::Applied, $this->repository->append($second)->status);
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        $query = $this->connection->prepare('SELECT count(*) FROM administration_console.outbox_messages WHERE message_id=:id');
        $query->execute(['id' => (new AdministrationConsoleOutboxPolicy)->messageId($second)]);
        self::assertSame(0, (int) $query->fetchColumn());
    }

    private function operator(): AdministrationOperatorDeliveryV1
    {
        return new AdministrationOperatorDeliveryV1(AdministrationOperatorEventType::Observed, new AdministrationOperatorDeliveryPayload(AdministrationOperatorDeliveryStatus::Available, '2026-08-03T12:00:00.123456Z'));
    }

    private function queue(): AdministrationQueueDeliveryV1
    {
        return new AdministrationQueueDeliveryV1(AdministrationQueueEventType::Observed, new AdministrationQueueDeliveryPayload(AdministrationQueueDeliveryStatus::Ready, '2026-08-03T12:00:00.123456Z'));
    }

    private function audit(): AdministrationAuditDeliveryV1
    {
        return new AdministrationAuditDeliveryV1(AdministrationAuditEventType::Observed, new AdministrationAuditDeliveryPayload(AdministrationAuditDeliveryStatus::Available, '2026-08-03T12:00:00.123456Z'));
    }
}
