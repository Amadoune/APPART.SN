<?php

namespace Tests\PostgreSQL\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionRetry\PublicProjectionDeterministicRetryPolicy;
use App\Application\PublicProjectionRetry\PublicProjectionFixedBackoff;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateOutcome;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolution;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolutionStatus;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolver;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionUpdaterConsumer;
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
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Application\PublicProjectionUpdaterIntegration\Support\FakePublicProjectionSourceLookup;
use Tests\Unit\Application\PublicProjectionUpdaterIntegration\Support\FakePublicProjectionUpdateExecutor;
use Tests\Unit\Application\PublicProjectionWorker\Support\FakePublicProjectionDeliveryClock;

final class PostgreSqlPublicProjectionUpdaterConsumerTest extends TestCase
{
    public function test_real_outbox_worker_consumer_and_updater_pipeline_is_durable(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;
        $writer = new PostgreSqlPublicProjectionOutboxWriter($connection, $mapper);
        $consumerId = PublicProjectionOutboxConsumerId::fromString('public-projection');
        $updater = new FakePublicProjectionUpdateExecutor(PublicListingProjectionUpdateOutcome::Applied);
        $consumer = new PublicProjectionUpdaterConsumer(new PublicProjectionSourceResolver(new PublicProjectionDeliveryEventCatalog, new FakePublicProjectionSourceLookup(new PublicProjectionSourceResolution(PublicProjectionSourceResolutionStatus::Resolved, $this->listingId()))), $updater);
        $registry = new PublicProjectionDeliveryConsumerRegistry([
            new PublicProjectionDeliveryConsumerRegistration($consumerId, PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), $consumer),
        ]);
        $worker = new PublicProjectionDeliveryWorker(new PostgreSqlPublicProjectionOutboxReader($connection, $mapper), new PostgreSqlPublicProjectionOutboxClaimManager($connection), $writer, $registry, new PublicProjectionDeterministicRetryPolicy(3, new PublicProjectionFixedBackoff(1)), new FakePublicProjectionDeliveryClock(new DateTimeImmutable('now')), PublicProjectionDeliveryWorkerId::fromString('worker:updater-integration'), 10, 60);
        $writer->append($this->message(), $consumerId);

        $result = $worker->runOnce($consumerId);

        self::assertSame(1, $result->count(PublicProjectionDeliveryOutcome::Delivered));
        self::assertSame(1, $updater->calls);
        self::assertSame(0, $worker->runOnce($consumerId)->requested);
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $fact = new PublicProjectionDeliveryPublishableFact(PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'), PublicProjectionDeliveryAggregateType::fromString('Listing'), PublicProjectionDeliveryAggregateId::fromString($this->listingId()), new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)), new DateTimeImmutable('2026-07-19T10:00:00+00:00'), new PublicProjectionDeliveryListingPayload($this->listingId()));

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable('2026-07-19T10:01:00+00:00'));
    }

    private function listingId(): string
    {
        return '44000000-0000-4000-8000-000000000001';
    }
}
