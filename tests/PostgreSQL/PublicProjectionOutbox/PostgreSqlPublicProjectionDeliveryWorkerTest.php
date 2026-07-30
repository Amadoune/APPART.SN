<?php

namespace Tests\PostgreSQL\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimOwnerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxLease;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistration;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistry;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryOutcome;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorker;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorkerId;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxClaimManager;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxReader;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Application\PublicProjectionWorker\Support\FakePublicProjectionDeliveryClock;
use Tests\Unit\Application\PublicProjectionWorker\Support\FakePublicProjectionOutboxRetryPolicy;
use Tests\Unit\Contracts\PublicProjectionDelivery\Support\FakePublicProjectionDeliveryConsumer;

final class PostgreSqlPublicProjectionDeliveryWorkerTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_bounded_worker_delivers_on_real_postgresql(): void
    {
        [$worker, $writer, $consumerId] = $this->harness(new FakePublicProjectionDeliveryConsumer(new PublicProjectionDeliveryEventCatalog));
        $message = $this->message('41000000-0000-4000-8000-000000000001');
        $writer->append($message, $consumerId);

        $result = $worker->runOnce($consumerId);

        self::assertSame(1, $result->claimed);
        self::assertSame(1, $result->count(PublicProjectionDeliveryOutcome::Delivered));
        self::assertSame(0, $worker->runOnce($consumerId)->requested);
    }

    public function test_expired_crash_after_effect_is_redelivered_without_second_effect(): void
    {
        $consumer = new FakePublicProjectionDeliveryConsumer(new PublicProjectionDeliveryEventCatalog);
        [$worker, $writer, $consumerId] = $this->harness($consumer);
        $message = $this->message('41000000-0000-4000-8000-000000000002');
        $writer->append($message, $consumerId);
        $past = new DateTimeImmutable('now -2 minutes');
        $lease = new PublicProjectionOutboxLease(PublicProjectionOutboxClaimOwnerId::fromString('crashed'), $past, $past->modify('+1 minute'));
        (new PostgreSqlPublicProjectionOutboxClaimManager($this->connection))->claim($message, $consumerId, $lease);
        self::assertSame(PublicProjectionDeliveryConsumptionResult::Consumed, $consumer->consume($message));

        $result = $worker->runOnce($consumerId);

        self::assertSame(1, $result->claimed);
        self::assertSame(1, $result->count(PublicProjectionDeliveryOutcome::LeaseExpired));
        self::assertSame(0, $worker->runOnce($consumerId)->requested);
    }

    /** @return array{PublicProjectionDeliveryWorker, PostgreSqlPublicProjectionOutboxWriter, PublicProjectionOutboxConsumerId} */
    private function harness(FakePublicProjectionDeliveryConsumer $consumer): array
    {
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;
        $writer = new PostgreSqlPublicProjectionOutboxWriter($this->connection, $mapper);
        $consumerId = PublicProjectionOutboxConsumerId::fromString('public-projection');
        $registry = new PublicProjectionDeliveryConsumerRegistry([
            new PublicProjectionDeliveryConsumerRegistration($consumerId, PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), $consumer),
        ]);
        $clock = new FakePublicProjectionDeliveryClock(new DateTimeImmutable('now'));
        $worker = new PublicProjectionDeliveryWorker(new PostgreSqlPublicProjectionOutboxReader($this->connection, $mapper), new PostgreSqlPublicProjectionOutboxClaimManager($this->connection), $writer, $registry, new FakePublicProjectionOutboxRetryPolicy, $clock, PublicProjectionDeliveryWorkerId::fromString('worker:postgresql'), 10, 60);

        return [$worker, $writer, $consumerId];
    }

    private function message(string $id): PublicProjectionDeliveryMessage
    {
        $fact = new PublicProjectionDeliveryPublishableFact(PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'), PublicProjectionDeliveryAggregateType::fromString('Listing'), PublicProjectionDeliveryAggregateId::fromString($id), new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)), new DateTimeImmutable('2026-07-19T10:00:00+00:00'), new PublicProjectionDeliveryListingPayload($id));

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable('2026-07-19T10:01:00+00:00'));
    }
}
