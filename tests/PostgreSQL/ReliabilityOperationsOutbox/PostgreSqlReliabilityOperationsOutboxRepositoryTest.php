<?php

namespace Tests\PostgreSQL\ReliabilityOperationsOutbox;

use Appart\Modules\ReliabilityOperations\Application\Delivery\ObservabilityDeliveryPayload;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ObservabilityDeliveryStatus;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ObservabilityDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Event\ObservabilityEventType;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxAppendResult;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxClaimResult;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxPolicy;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxRetryResult;
use Appart\Modules\ReliabilityOperations\Infrastructure\Outbox\PostgreSqlReliabilityOperationsOutboxRepository;
use Appart\Modules\ReliabilityOperations\Infrastructure\Outbox\ReliabilityOperationsOutboxMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlReliabilityOperationsOutboxRepositoryTest extends TestCase
{
    private PDO $connection;

    private ReliabilityOperationsOutboxMapper $mapper;

    private PostgreSqlReliabilityOperationsOutboxRepository $repository;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        $this->connection->exec((string) file_get_contents(self::downMigration()));
        $this->connection->exec((string) file_get_contents(self::migration()));
        $this->mapper = new ReliabilityOperationsOutboxMapper;
        $this->repository = new PostgreSqlReliabilityOperationsOutboxRepository($this->connection, $this->mapper, new ReliabilityOperationsOutboxPolicy);
    }

    public function test_append_idempotence_divergence_uniqueness_and_ordered_read(): void
    {
        $delivery = self::delivery(ObservabilityDeliveryStatus::Available, '10:00:00');
        self::assertSame(ReliabilityOperationsOutboxAppendResult::Applied, $this->repository->append($delivery, self::at('10:00:01')));
        self::assertSame(ReliabilityOperationsOutboxAppendResult::AlreadyApplied, $this->repository->append($delivery, self::at('10:00:01')));
        self::assertSame(ReliabilityOperationsOutboxAppendResult::DivergentMessage, $this->repository->append(self::delivery(ObservabilityDeliveryStatus::Missing, '10:00:00'), self::at('10:00:01')));
        self::assertSame(ReliabilityOperationsOutboxAppendResult::Applied, $this->repository->append(self::delivery(ObservabilityDeliveryStatus::Available, '10:00:02'), self::at('10:00:03')));
        self::assertSame(2, (int) $this->connection->query('SELECT count(*) FROM reliability_operations.outbox_message_journal')->fetchColumn());
        self::assertSame(2, (int) $this->connection->query('SELECT count(DISTINCT event_id) FROM reliability_operations.outbox_message_journal')->fetchColumn());
        $messages = $this->repository->eligible(self::at('11:00:00'), 10);
        self::assertCount(2, $messages);
        self::assertSame('2026-08-06T10:00:00.123456Z', $messages[0]->observedAt);
        self::assertSame('2026-08-06T10:00:02.123456Z', $messages[1]->observedAt);
    }

    public function test_claim_is_exclusive_and_retry_is_bounded_to_ten(): void
    {
        $delivery = self::delivery(ObservabilityDeliveryStatus::Available, '10:00:00');
        $this->repository->append($delivery, self::at('10:00:01'));
        $id = $this->mapper->fromDelivery($delivery, self::at('10:00:01'))->messageId;
        for ($attempt = 1; $attempt <= 9; $attempt++) {
            self::assertSame(ReliabilityOperationsOutboxClaimResult::Claimed, $this->repository->claim($id, self::at('10:01:00')));
            self::assertSame(ReliabilityOperationsOutboxClaimResult::AlreadyClaimed, $this->repository->claim($id, self::at('10:01:00')));
            self::assertSame(ReliabilityOperationsOutboxRetryResult::RetryScheduled, $this->repository->retry($id, self::at('10:01:00')));
        }
        self::assertSame(ReliabilityOperationsOutboxClaimResult::Claimed, $this->repository->claim($id, self::at('10:01:00')));
        self::assertSame(ReliabilityOperationsOutboxRetryResult::AttemptsExhausted, $this->repository->retry($id, self::at('10:01:00')));
        self::assertSame(ReliabilityOperationsOutboxClaimResult::AttemptsExhausted, $this->repository->claim($id, self::at('10:01:00')));
    }

    public function test_skip_locked_and_external_rollback_are_preserved(): void
    {
        $delivery = self::delivery(ObservabilityDeliveryStatus::Available, '10:00:00');
        $this->repository->append($delivery, self::at('10:00:01'));
        $id = $this->mapper->fromDelivery($delivery, self::at('10:00:01'))->messageId;
        $this->connection->beginTransaction();
        self::assertSame(ReliabilityOperationsOutboxClaimResult::Claimed, $this->repository->claim($id, self::at('10:01:00')));
        self::assertTrue($this->connection->inTransaction());
        $other = new PostgreSqlReliabilityOperationsOutboxRepository(PostgreSqlTestEnvironment::connection(), $this->mapper, new ReliabilityOperationsOutboxPolicy);
        self::assertSame(ReliabilityOperationsOutboxClaimResult::AlreadyClaimed, $other->claim($id, self::at('10:01:00')));
        $this->connection->rollBack();

        $this->connection->beginTransaction();
        self::assertSame(ReliabilityOperationsOutboxAppendResult::Applied, $this->repository->append(self::delivery(ObservabilityDeliveryStatus::Available, '10:02:00'), self::at('10:02:01')));
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM reliability_operations.outbox_message_journal')->fetchColumn());
    }

    public function test_migration_and_rollback_089_are_executable(): void
    {
        $this->connection->exec((string) file_get_contents(self::downMigration()));
        self::assertNull($this->connection->query("SELECT to_regclass('reliability_operations.outbox_message_journal')")->fetchColumn());
        $this->connection->exec((string) file_get_contents(self::migration()));
        self::assertSame('reliability_operations.outbox_message_journal', $this->connection->query("SELECT to_regclass('reliability_operations.outbox_message_journal')")->fetchColumn());
    }

    private static function delivery(ObservabilityDeliveryStatus $status, string $time): ObservabilityDeliveryV1
    {
        return new ObservabilityDeliveryV1(ObservabilityEventType::Observed, new ObservabilityDeliveryPayload($status, '2026-08-06T'.$time.'.123456Z'));
    }

    private static function at(string $time): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-06T'.$time.'.123456Z');
    }

    private static function migration(): string
    {
        return dirname(__DIR__, 3).'/src/Modules/ReliabilityOperations/Infrastructure/Outbox/Migrations/089_reliability_operations_outbox.sql';
    }

    private static function downMigration(): string
    {
        return dirname(__DIR__, 3).'/src/Modules/ReliabilityOperations/Infrastructure/Outbox/Migrations/089_reliability_operations_outbox.down.sql';
    }
}
