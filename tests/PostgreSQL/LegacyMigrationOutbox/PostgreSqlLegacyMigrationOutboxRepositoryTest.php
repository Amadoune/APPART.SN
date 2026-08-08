<?php

namespace Tests\PostgreSQL\LegacyMigrationOutbox;

use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationCutoverDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationCutoverDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationCutoverDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationInventoryDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationInventoryDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationInventoryDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationQuarantineDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationQuarantineDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationQuarantineDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationReconciliationDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationReconciliationDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationReconciliationDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationWaveDeliveryPayload;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationWaveDeliveryStatus;
use Appart\Modules\LegacyMigration\Application\Delivery\LegacyMigrationWaveDeliveryV1;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationCutoverEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationInventoryEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationQuarantineEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationReconciliationEventType;
use Appart\Modules\LegacyMigration\Application\Event\LegacyMigrationWaveEventType;
use Appart\Modules\LegacyMigration\Application\Outbox\LegacyMigrationOutboxPolicy;
use Appart\Modules\LegacyMigration\Application\Outbox\LegacyMigrationOutboxStatus;
use Appart\Modules\LegacyMigration\Infrastructure\Outbox\PostgreSqlLegacyMigrationOutboxRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlLegacyMigrationOutboxRepositoryTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlLegacyMigrationOutboxRepository $repository;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        $root = dirname(__DIR__, 3);
        $this->connection->exec((string) file_get_contents($root.'/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/Migrations/084_legacy_migration_owner_source.sql'));
        $this->connection->exec((string) file_get_contents($root.'/src/Modules/LegacyMigration/Infrastructure/Outbox/Migrations/085_legacy_migration_outbox.sql'));
        $this->connection->exec('TRUNCATE legacy_migration.outbox_messages');
        $this->repository = new PostgreSqlLegacyMigrationOutboxRepository($this->connection, new LegacyMigrationOutboxPolicy);
    }

    public function test_append_is_idempotent_and_pending_reconstructs_five_streams_in_order(): void
    {
        $deliveries = [$this->inventory(), $this->wave(), $this->reconciliation(), $this->quarantine(), $this->cutover()];
        foreach ($deliveries as $delivery) {
            self::assertSame(LegacyMigrationOutboxStatus::Applied, $this->repository->append($delivery)->status);
            self::assertSame(LegacyMigrationOutboxStatus::AlreadyApplied, $this->repository->append($delivery)->status);
        }
        $pending = $this->repository->pending(10);
        self::assertCount(5, $pending);
        self::assertSame(array_map(static fn ($entry): string => $entry->messageId, $pending), array_values(array_unique(array_map(static fn ($entry): string => $entry->messageId, $pending))));
        foreach ($pending as $entry) {
            self::assertSame(self::observedAt(), $entry->delivery->payload->observedAt);
        }
    }

    public function test_divergence_retry_bound_and_external_rollback_are_preserved(): void
    {
        $delivery = $this->inventory();
        $entry = $this->repository->append($delivery);
        $statement = $this->connection->prepare('UPDATE legacy_migration.outbox_messages SET message_checksum=:checksum WHERE message_id=:id');
        $statement->execute(['checksum' => str_repeat('0', 64), 'id' => $entry->messageId]);
        self::assertSame(LegacyMigrationOutboxStatus::DivergentMessage, $this->repository->append($delivery)->status);
        $this->connection->exec('UPDATE legacy_migration.outbox_messages SET retry_count=10');
        self::assertSame([], $this->repository->pending(10));

        $second = $this->wave();
        $this->connection->beginTransaction();
        self::assertSame(LegacyMigrationOutboxStatus::Applied, $this->repository->append($second)->status);
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        $query = $this->connection->prepare('SELECT count(*) FROM legacy_migration.outbox_messages WHERE message_id=:id');
        $query->execute(['id' => (new LegacyMigrationOutboxPolicy)->messageId($second)]);
        self::assertSame(0, (int) $query->fetchColumn());
    }

    private function inventory(): LegacyMigrationInventoryDeliveryV1
    {
        return new LegacyMigrationInventoryDeliveryV1(LegacyMigrationInventoryEventType::Observed, new LegacyMigrationInventoryDeliveryPayload(LegacyMigrationInventoryDeliveryStatus::Available, self::observedAt()));
    }

    private function wave(): LegacyMigrationWaveDeliveryV1
    {
        return new LegacyMigrationWaveDeliveryV1(LegacyMigrationWaveEventType::Observed, new LegacyMigrationWaveDeliveryPayload(LegacyMigrationWaveDeliveryStatus::Ready, self::observedAt()));
    }

    private function reconciliation(): LegacyMigrationReconciliationDeliveryV1
    {
        return new LegacyMigrationReconciliationDeliveryV1(LegacyMigrationReconciliationEventType::Observed, new LegacyMigrationReconciliationDeliveryPayload(LegacyMigrationReconciliationDeliveryStatus::Matched, self::observedAt()));
    }

    private function quarantine(): LegacyMigrationQuarantineDeliveryV1
    {
        return new LegacyMigrationQuarantineDeliveryV1(LegacyMigrationQuarantineEventType::Observed, new LegacyMigrationQuarantineDeliveryPayload(LegacyMigrationQuarantineDeliveryStatus::Empty, self::observedAt()));
    }

    private function cutover(): LegacyMigrationCutoverDeliveryV1
    {
        return new LegacyMigrationCutoverDeliveryV1(LegacyMigrationCutoverEventType::Observed, new LegacyMigrationCutoverDeliveryPayload(LegacyMigrationCutoverDeliveryStatus::Ready, self::observedAt()));
    }

    private static function observedAt(): string
    {
        return '2026-08-04T12:00:00.123456Z';
    }
}
