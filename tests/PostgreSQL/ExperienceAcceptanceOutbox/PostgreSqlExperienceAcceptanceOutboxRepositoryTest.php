<?php

namespace Tests\PostgreSQL\ExperienceAcceptanceOutbox;

use Appart\Modules\ExperienceAcceptance\Application\Delivery\ResponsiveComplianceDeliveryPayload;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ResponsiveComplianceDeliveryStatus;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ResponsiveComplianceDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Event\ResponsiveComplianceEventType;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxAppendResult;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxClaimResult;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxPolicy;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxRetryResult;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Outbox\ExperienceAcceptanceOutboxMapper;
use Appart\Modules\ExperienceAcceptance\Infrastructure\Outbox\PostgreSqlExperienceAcceptanceOutboxRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlExperienceAcceptanceOutboxRepositoryTest extends TestCase
{
    private PDO $connection;

    private ExperienceAcceptanceOutboxMapper $mapper;

    private PostgreSqlExperienceAcceptanceOutboxRepository $repository;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        $this->connection->exec((string) file_get_contents(self::downMigration()));
        $this->connection->exec((string) file_get_contents(self::migration()));
        $this->mapper = new ExperienceAcceptanceOutboxMapper;
        $this->repository = new PostgreSqlExperienceAcceptanceOutboxRepository($this->connection, $this->mapper, new ExperienceAcceptanceOutboxPolicy);
    }

    public function test_append_idempotence_divergence_uniqueness_and_ordered_read(): void
    {
        $delivery = self::delivery(ResponsiveComplianceDeliveryStatus::Available, '10:00:00');
        self::assertSame(ExperienceAcceptanceOutboxAppendResult::Applied, $this->repository->append($delivery, self::at('10:00:01')));
        self::assertSame(ExperienceAcceptanceOutboxAppendResult::AlreadyApplied, $this->repository->append($delivery, self::at('10:00:01')));
        self::assertSame(ExperienceAcceptanceOutboxAppendResult::DivergentMessage, $this->repository->append(self::delivery(ResponsiveComplianceDeliveryStatus::Missing, '10:00:00'), self::at('10:00:01')));
        self::assertSame(ExperienceAcceptanceOutboxAppendResult::Applied, $this->repository->append(self::delivery(ResponsiveComplianceDeliveryStatus::Available, '10:00:02'), self::at('10:00:03')));
        self::assertSame(2, (int) $this->connection->query('SELECT count(*) FROM experience_acceptance.outbox_message_journal')->fetchColumn());
        self::assertSame(2, (int) $this->connection->query('SELECT count(DISTINCT event_id) FROM experience_acceptance.outbox_message_journal')->fetchColumn());
        $messages = $this->repository->eligible(self::at('11:00:00'), 10);
        self::assertCount(2, $messages);
        self::assertSame('2026-08-06T10:00:00.123456Z', $messages[0]->observedAt);
        self::assertSame('2026-08-06T10:00:02.123456Z', $messages[1]->observedAt);
    }

    public function test_claim_is_exclusive_and_retry_is_bounded_to_ten(): void
    {
        $delivery = self::delivery(ResponsiveComplianceDeliveryStatus::Available, '10:00:00');
        $this->repository->append($delivery, self::at('10:00:01'));
        $id = $this->mapper->fromDelivery($delivery, self::at('10:00:01'))->messageId;
        for ($attempt = 1; $attempt <= 9; $attempt++) {
            self::assertSame(ExperienceAcceptanceOutboxClaimResult::Claimed, $this->repository->claim($id, self::at('10:01:00')));
            self::assertSame(ExperienceAcceptanceOutboxClaimResult::AlreadyClaimed, $this->repository->claim($id, self::at('10:01:00')));
            self::assertSame(ExperienceAcceptanceOutboxRetryResult::RetryScheduled, $this->repository->retry($id, self::at('10:01:00')));
        }
        self::assertSame(ExperienceAcceptanceOutboxClaimResult::Claimed, $this->repository->claim($id, self::at('10:01:00')));
        self::assertSame(ExperienceAcceptanceOutboxRetryResult::AttemptsExhausted, $this->repository->retry($id, self::at('10:01:00')));
        self::assertSame(ExperienceAcceptanceOutboxClaimResult::AttemptsExhausted, $this->repository->claim($id, self::at('10:01:00')));
    }

    public function test_skip_locked_and_external_rollback_are_preserved(): void
    {
        $delivery = self::delivery(ResponsiveComplianceDeliveryStatus::Available, '10:00:00');
        $this->repository->append($delivery, self::at('10:00:01'));
        $id = $this->mapper->fromDelivery($delivery, self::at('10:00:01'))->messageId;
        $this->connection->beginTransaction();
        self::assertSame(ExperienceAcceptanceOutboxClaimResult::Claimed, $this->repository->claim($id, self::at('10:01:00')));
        self::assertTrue($this->connection->inTransaction());
        $other = new PostgreSqlExperienceAcceptanceOutboxRepository(PostgreSqlTestEnvironment::connection(), $this->mapper, new ExperienceAcceptanceOutboxPolicy);
        self::assertSame(ExperienceAcceptanceOutboxClaimResult::AlreadyClaimed, $other->claim($id, self::at('10:01:00')));
        $this->connection->rollBack();

        $this->connection->beginTransaction();
        self::assertSame(ExperienceAcceptanceOutboxAppendResult::Applied, $this->repository->append(self::delivery(ResponsiveComplianceDeliveryStatus::Available, '10:02:00'), self::at('10:02:01')));
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM experience_acceptance.outbox_message_journal')->fetchColumn());
    }

    public function test_migration_and_rollback_091_are_executable(): void
    {
        $this->connection->exec((string) file_get_contents(self::downMigration()));
        self::assertNull($this->connection->query("SELECT to_regclass('experience_acceptance.outbox_message_journal')")->fetchColumn());
        $this->connection->exec((string) file_get_contents(self::migration()));
        self::assertSame('experience_acceptance.outbox_message_journal', $this->connection->query("SELECT to_regclass('experience_acceptance.outbox_message_journal')")->fetchColumn());
    }

    private static function delivery(ResponsiveComplianceDeliveryStatus $status, string $time): ResponsiveComplianceDeliveryV1
    {
        return new ResponsiveComplianceDeliveryV1(ResponsiveComplianceEventType::Observed, new ResponsiveComplianceDeliveryPayload($status, '2026-08-06T'.$time.'.123456Z'));
    }

    private static function at(string $time): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-06T'.$time.'.123456Z');
    }

    private static function migration(): string
    {
        return dirname(__DIR__, 3).'/src/Modules/ExperienceAcceptance/Infrastructure/Outbox/Migrations/091_experience_acceptance_outbox.sql';
    }

    private static function downMigration(): string
    {
        return dirname(__DIR__, 3).'/src/Modules/ExperienceAcceptance/Infrastructure/Outbox/Migrations/091_experience_acceptance_outbox.down.sql';
    }
}
