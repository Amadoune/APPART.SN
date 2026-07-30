<?php

namespace Tests\PostgreSQL\MediaIngestionEventOutbox;

use App\Application\MediaIngestionEventIntegration\MediaIngestionAtomicDelivery;
use App\Application\MediaIngestionEventIntegration\MediaIngestionAtomicDeliveryResult;
use App\Application\MediaIngestionEventOutbox\MediaIngestionOutboxWriteResult;
use App\Application\MediaIngestionEventRouting\DeterministicMediaIngestionEventRouter;
use App\Application\MediaIngestionEventRouting\MediaIngestionRoutingDestination;
use App\Application\MediaIngestionEventTransport\MediaIngestionDeliveryMessageV1;
use App\Application\MediaIngestionEventTransport\MediaIngestionEventTransportSerializer;
use App\Infrastructure\MediaIngestionEventOutbox\PostgreSql\PostgreSqlMediaIngestionAtomicDeliveryTransaction;
use App\Infrastructure\MediaIngestionEventOutbox\PostgreSql\PostgreSqlMediaIngestionOutbox;
use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventType;
use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventV1;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlMediaIngestionEventOutboxTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlMediaIngestionOutbox $outbox;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->outbox = new PostgreSqlMediaIngestionOutbox(
            $this->connection,
            new MediaIngestionEventTransportSerializer,
        );
    }

    public function test_append_is_atomic_and_idempotent(): void
    {
        $message = new MediaIngestionDeliveryMessageV1($this->event());
        $destinations = (new DeterministicMediaIngestionEventRouter(new MediaIngestionEventTransportSerializer))
            ->route($message)->destinations;

        self::assertSame(MediaIngestionOutboxWriteResult::Applied, $this->outbox->append($message, $destinations));
        self::assertSame(MediaIngestionOutboxWriteResult::AlreadyApplied, $this->outbox->append($message, $destinations));
        self::assertSame(1, $this->tableCount('event_outbox_messages'));
        self::assertSame(3, $this->tableCount('event_outbox_deliveries'));
        self::assertFalse($this->connection->inTransaction());
    }

    public function test_event_id_collision_is_a_divergent_message(): void
    {
        $first = new MediaIngestionDeliveryMessageV1($this->event());
        $divergent = new MediaIngestionDeliveryMessageV1($this->event('2026-07-28T10:00:02+00:00'));
        $router = new DeterministicMediaIngestionEventRouter(new MediaIngestionEventTransportSerializer);

        self::assertSame(MediaIngestionOutboxWriteResult::Applied, $this->outbox->append($first, $router->route($first)->destinations));
        self::assertSame(MediaIngestionOutboxWriteResult::DivergentMessage, $this->outbox->append($divergent, $router->route($divergent)->destinations));
        self::assertSame(1, $this->tableCount('event_outbox_messages'));
    }

    public function test_claim_retry_expired_lease_delivery_and_quarantine_are_deterministic(): void
    {
        $message = new MediaIngestionDeliveryMessageV1($this->event());
        $this->outbox->append($message, [MediaIngestionRoutingDestination::PrivateAudit]);
        $now = new DateTimeImmutable('2026-07-28T10:01:00+00:00');

        $first = $this->outbox->claimNext('worker-a', $now);
        self::assertNotNull($first);
        self::assertSame(1, $first->attempt);
        self::assertTrue($this->outbox->scheduleRetry($first, $now->modify('+1 second'), 'temporary'));
        self::assertNull($this->outbox->claimNext('worker-b', $now));

        $second = $this->outbox->claimNext('worker-b', $now->modify('+2 seconds'));
        self::assertNotNull($second);
        self::assertSame(2, $second->attempt);
        self::assertTrue($this->outbox->markDelivered($second, $now->modify('+3 seconds')));
        self::assertNull($this->outbox->claimNext('worker-c', $now->modify('+4 seconds')));

        $quarantinedMessage = new MediaIngestionDeliveryMessageV1($this->event(aggregateVersion: 2));
        self::assertSame(
            MediaIngestionOutboxWriteResult::Applied,
            $this->outbox->append($quarantinedMessage, [MediaIngestionRoutingDestination::PrivateAudit]),
        );
        $third = $this->outbox->claimNext('worker-c', $now->modify('+5 seconds'));
        self::assertNotNull($third);
        self::assertTrue($this->outbox->quarantine($third, 'terminal'));
    }

    public function test_atomic_delivery_rolls_back_rejected_work_and_persists_applied_work(): void
    {
        $atomic = new MediaIngestionAtomicDelivery(
            new PostgreSqlMediaIngestionAtomicDeliveryTransaction($this->connection),
            new DeterministicMediaIngestionEventRouter(new MediaIngestionEventTransportSerializer),
            $this->outbox,
        );

        self::assertSame(
            MediaIngestionAtomicDeliveryResult::Rejected,
            $atomic->execute(static fn (): MediaIngestionAtomicDeliveryResult => MediaIngestionAtomicDeliveryResult::Rejected, [$this->event()]),
        );
        self::assertSame(0, $this->tableCount('event_outbox_messages'));
        self::assertSame(
            MediaIngestionAtomicDeliveryResult::Applied,
            $atomic->execute(static fn (): MediaIngestionAtomicDeliveryResult => MediaIngestionAtomicDeliveryResult::Applied, [$this->event()]),
        );
        self::assertSame(1, $this->tableCount('event_outbox_messages'));
        self::assertFalse($this->connection->inTransaction());
    }

    private function event(
        string $recordedAt = '2026-07-28T10:00:01+00:00',
        int $aggregateVersion = 1,
    ): MediaIngestionEventV1 {
        return new MediaIngestionEventV1(
            MediaIngestionEventType::AssetReady,
            '5b000000-0000-4000-8000-000000000011',
            $aggregateVersion,
            'media-policy-v1',
            new DateTimeImmutable('2026-07-28T10:00:00+00:00'),
            new DateTimeImmutable($recordedAt),
            '5b000000-0000-4000-8000-000000000012',
            '5b000000-0000-4000-8000-000000000013',
        );
    }

    private function tableCount(string $table): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM media_ingestion.{$table}")->fetchColumn();
    }
}
