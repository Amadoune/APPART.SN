<?php

namespace Tests\PostgreSQL\SecurityComplianceOutbox;

use Appart\Modules\SecurityCompliance\Application\Delivery\SecretInventoryDeliveryPayload;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecretInventoryDeliveryStatus;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecretInventoryDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Event\SecretInventoryEventType;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxAppendResult;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxClaimResult;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxRetryResult;
use Appart\Modules\SecurityCompliance\Infrastructure\Outbox\PostgreSqlSecurityComplianceOutboxRepository;
use Appart\Modules\SecurityCompliance\Infrastructure\Outbox\SecurityComplianceOutboxMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlSecurityComplianceOutboxRepositoryTest extends TestCase
{
    private PDO $connection;

    private SecurityComplianceOutboxMapper $mapper;

    private PostgreSqlSecurityComplianceOutboxRepository $repository;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        $this->connection->exec((string) file_get_contents(self::downMigration()));
        $this->connection->exec((string) file_get_contents(self::migration()));
        $this->mapper = new SecurityComplianceOutboxMapper;
        $this->repository = new PostgreSqlSecurityComplianceOutboxRepository($this->connection, $this->mapper);
    }

    public function test_append_idempotence_divergence_uniqueness_and_ordered_read(): void
    {
        $delivery = self::delivery(SecretInventoryDeliveryStatus::Available, '10:00:00');
        self::assertSame(SecurityComplianceOutboxAppendResult::Applied, $this->repository->append($delivery, self::at('10:00:01')));
        self::assertSame(SecurityComplianceOutboxAppendResult::AlreadyApplied, $this->repository->append($delivery, self::at('10:00:01')));
        self::assertSame(SecurityComplianceOutboxAppendResult::DivergentMessage, $this->repository->append(self::delivery(SecretInventoryDeliveryStatus::Missing, '10:00:00'), self::at('10:00:01')));
        self::assertSame(SecurityComplianceOutboxAppendResult::Applied, $this->repository->append(self::delivery(SecretInventoryDeliveryStatus::Available, '10:00:02'), self::at('10:00:03')));
        self::assertSame(2, (int) $this->connection->query('SELECT count(*) FROM security_compliance.outbox_message_journal')->fetchColumn());
        self::assertSame(2, (int) $this->connection->query('SELECT count(DISTINCT event_id) FROM security_compliance.outbox_message_journal')->fetchColumn());
        $messages = $this->repository->eligible(self::at('11:00:00'), 10);
        self::assertCount(2, $messages);
        self::assertSame('2026-08-06T10:00:00.123456Z', $messages[0]->observedAt);
        self::assertSame('2026-08-06T10:00:02.123456Z', $messages[1]->observedAt);
    }

    public function test_claim_is_exclusive_and_retry_is_bounded_to_ten(): void
    {
        $delivery = self::delivery(SecretInventoryDeliveryStatus::Available, '10:00:00');
        $this->repository->append($delivery, self::at('10:00:01'));
        $id = $this->mapper->fromDelivery($delivery, self::at('10:00:01'))->messageId;
        for ($attempt = 1; $attempt <= 9; $attempt++) {
            self::assertSame(SecurityComplianceOutboxClaimResult::Claimed, $this->repository->claim($id, self::at('10:01:00')));
            self::assertSame(SecurityComplianceOutboxClaimResult::AlreadyClaimed, $this->repository->claim($id, self::at('10:01:00')));
            self::assertSame(SecurityComplianceOutboxRetryResult::RetryScheduled, $this->repository->retry($id, self::at('10:01:00')));
        }
        self::assertSame(SecurityComplianceOutboxClaimResult::Claimed, $this->repository->claim($id, self::at('10:01:00')));
        self::assertSame(SecurityComplianceOutboxRetryResult::AttemptsExhausted, $this->repository->retry($id, self::at('10:01:00')));
        self::assertSame(SecurityComplianceOutboxClaimResult::AttemptsExhausted, $this->repository->claim($id, self::at('10:01:00')));
    }

    public function test_skip_locked_and_external_rollback_are_preserved(): void
    {
        $delivery = self::delivery(SecretInventoryDeliveryStatus::Available, '10:00:00');
        $this->repository->append($delivery, self::at('10:00:01'));
        $id = $this->mapper->fromDelivery($delivery, self::at('10:00:01'))->messageId;
        $this->connection->beginTransaction();
        self::assertSame(SecurityComplianceOutboxClaimResult::Claimed, $this->repository->claim($id, self::at('10:01:00')));
        self::assertTrue($this->connection->inTransaction());
        $other = new PostgreSqlSecurityComplianceOutboxRepository(PostgreSqlTestEnvironment::connection(), $this->mapper);
        self::assertSame(SecurityComplianceOutboxClaimResult::AlreadyClaimed, $other->claim($id, self::at('10:01:00')));
        $this->connection->rollBack();

        $this->connection->beginTransaction();
        self::assertSame(SecurityComplianceOutboxAppendResult::Applied, $this->repository->append(self::delivery(SecretInventoryDeliveryStatus::Available, '10:02:00'), self::at('10:02:01')));
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM security_compliance.outbox_message_journal')->fetchColumn());
    }

    public function test_migration_and_rollback_087_are_executable(): void
    {
        $this->connection->exec((string) file_get_contents(self::downMigration()));
        self::assertNull($this->connection->query("SELECT to_regclass('security_compliance.outbox_message_journal')")->fetchColumn());
        $this->connection->exec((string) file_get_contents(self::migration()));
        self::assertSame('security_compliance.outbox_message_journal', $this->connection->query("SELECT to_regclass('security_compliance.outbox_message_journal')")->fetchColumn());
    }

    private static function delivery(SecretInventoryDeliveryStatus $status, string $time): SecretInventoryDeliveryV1
    {
        return new SecretInventoryDeliveryV1(SecretInventoryEventType::Observed, new SecretInventoryDeliveryPayload($status, '2026-08-06T'.$time.'.123456Z'));
    }

    private static function at(string $time): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-06T'.$time.'.123456Z');
    }

    private static function migration(): string
    {
        return dirname(__DIR__, 3).'/src/Modules/SecurityCompliance/Infrastructure/Outbox/Migrations/087_security_compliance_outbox.sql';
    }

    private static function downMigration(): string
    {
        return dirname(__DIR__, 3).'/src/Modules/SecurityCompliance/Infrastructure/Outbox/Migrations/087_security_compliance_outbox.down.sql';
    }
}
