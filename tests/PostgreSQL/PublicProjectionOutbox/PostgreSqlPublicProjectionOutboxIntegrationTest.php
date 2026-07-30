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
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryStatus;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimOwnerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimResult;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxLease;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineReason;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryBackoff;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryClassification;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxParticipantTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxClaimManager;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxReader;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\ListingLifecycle\FakeListingRegistryHarness;

final class PostgreSqlPublicProjectionOutboxIntegrationTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlPublicProjectionOutboxWriter $writer;

    private PublicProjectionOutboxConsumerId $consumer;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->writer = new PostgreSqlPublicProjectionOutboxWriter($this->connection, new PostgreSqlPublicProjectionOutboxMapper);
        $this->consumer = PublicProjectionOutboxConsumerId::fromString('public-projection');
    }

    public function test_append_double_append_and_reader_round_trip_are_strict(): void
    {
        $message = $this->message();
        self::assertSame(PublicProjectionOutboxWriteResult::Applied, $this->writer->append($message, $this->consumer));
        self::assertSame(PublicProjectionOutboxWriteResult::AlreadyApplied, $this->writer->append($message, $this->consumer));

        $records = (new PostgreSqlPublicProjectionOutboxReader($this->connection, new PostgreSqlPublicProjectionOutboxMapper))->findClaimable($this->consumer, 10);
        self::assertCount(1, $records);
        self::assertEquals($message, $records[0]->message);
        self::assertSame(PublicProjectionDeliveryStatus::Pending, $records[0]->status);
        self::assertSame(1, $this->countRows('listing_lifecycle.public_projection_outbox_messages'));
    }

    public function test_claim_is_exclusive_expires_retries_releases_and_quarantines(): void
    {
        $message = $this->message();
        $this->writer->append($message, $this->consumer);
        $first = new PostgreSqlPublicProjectionOutboxClaimManager($this->connection);
        $second = new PostgreSqlPublicProjectionOutboxClaimManager(PostgreSqlTestEnvironment::connection());
        $leaseA = $this->lease('relay:a', 1, 5);
        $leaseB = $this->lease('relay:b', 2, 6);

        self::assertSame(PublicProjectionOutboxClaimResult::Claimed, $first->claim($message, $this->consumer, $leaseA));
        self::assertSame(PublicProjectionOutboxClaimResult::AlreadyClaimed, $second->claim($message, $this->consumer, $leaseB));
        self::assertSame(PublicProjectionOutboxClaimResult::LeaseExpired, $first->expire($message, $this->consumer, $this->at(5)));
        self::assertSame(PublicProjectionOutboxClaimResult::Claimed, $second->claim($message, $this->consumer, $leaseB));

        $retry = new PublicProjectionOutboxRetryDecision(PublicProjectionOutboxRetryClassification::Transient, new PublicProjectionOutboxRetryBackoff(0), true);
        self::assertSame(PublicProjectionOutboxWriteResult::Applied, $this->writer->scheduleRetry($message, $this->consumer, $leaseB->ownerId, $retry));
        self::assertSame(PublicProjectionDeliveryStatus::RetryScheduled, (new PostgreSqlPublicProjectionOutboxReader($this->connection, new PostgreSqlPublicProjectionOutboxMapper))->findRetryable($this->consumer)[0]->status);

        $leaseC = $this->lease('relay:c', 7, 9);
        self::assertSame(PublicProjectionOutboxClaimResult::Claimed, $first->claim($message, $this->consumer, $leaseC));
        self::assertSame(PublicProjectionOutboxWriteResult::Applied, $this->writer->releaseClaim($message, $this->consumer, $leaseC->ownerId));
        $decision = new PublicProjectionOutboxQuarantineDecision(PublicProjectionOutboxQuarantineReason::PermanentFailure, 'permanent_failure');
        self::assertSame(PublicProjectionOutboxWriteResult::Applied, $this->writer->quarantine($message, $this->consumer, null, $decision));
        self::assertCount(1, (new PostgreSqlPublicProjectionOutboxReader($this->connection, new PostgreSqlPublicProjectionOutboxMapper))->findQuarantined($this->consumer));
    }

    public function test_listing_and_outbox_commit_on_the_same_connection_and_transaction(): void
    {
        $fixtures = new FakeListingRegistryHarness;
        $listing = $fixtures->minimalListing();
        $participant = new PostgreSqlAggregateOutboxParticipantTransaction($this->connection);
        $repository = new PostgreSqlListingRepository($this->connection, new ListingMapper, $participant);
        $transaction = new PostgreSqlAggregateOutboxTransaction($this->connection);

        $transaction->run(function () use ($repository, $listing): void {
            $repository->add($listing);
            self::assertSame(PublicProjectionOutboxWriteResult::Applied, $this->writer->append($this->message($listing->id()->value), $this->consumer));
        });

        $external = PostgreSqlTestEnvironment::connection();
        self::assertSame(1, (int) $external->query('SELECT count(*) FROM listing_lifecycle.listings')->fetchColumn());
        self::assertSame(1, (int) $external->query('SELECT count(*) FROM listing_lifecycle.public_projection_outbox_messages')->fetchColumn());
    }

    public function test_aggregate_and_outbox_roll_back_together_and_nested_transactions_are_refused(): void
    {
        $fixtures = new FakeListingRegistryHarness;
        $listing = $fixtures->minimalListing();
        $participant = new PostgreSqlAggregateOutboxParticipantTransaction($this->connection);
        $repository = new PostgreSqlListingRepository($this->connection, new ListingMapper, $participant);
        $transaction = new PostgreSqlAggregateOutboxTransaction($this->connection);

        try {
            $transaction->run(function () use ($repository, $listing): void {
                $repository->add($listing);
                $this->writer->append($this->message($listing->id()->value), $this->consumer);
                throw new RuntimeException('controlled rollback');
            });
            self::fail('The transaction must roll back.');
        } catch (RuntimeException $error) {
            self::assertSame('controlled rollback', $error->getMessage());
        }
        self::assertSame(0, $this->countRows('listing_lifecycle.listings'));
        self::assertSame(0, $this->countRows('listing_lifecycle.public_projection_outbox_messages'));

        $this->connection->beginTransaction();
        try {
            $this->expectException(RuntimeException::class);
            $transaction->run(static fn (): null => null);
        } finally {
            $this->connection->rollBack();
        }
    }

    private function message(string $id = '32000000-0000-4000-8000-000000000001'): PublicProjectionDeliveryMessage
    {
        $type = PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested');
        $version = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $module = PublicProjectionDeliverySourceModule::fromString('ListingLifecycle');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('Listing');
        $aggregateId = PublicProjectionDeliveryAggregateId::fromString($id);
        $fact = new PublicProjectionDeliveryPublishableFact($type, $version, $module, $aggregate, $aggregateId, new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)), $this->at(0), new PublicProjectionDeliveryListingPayload($id));

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, $this->at(1));
    }

    private function lease(string $owner, int $claimed, int $expires): PublicProjectionOutboxLease
    {
        return new PublicProjectionOutboxLease(PublicProjectionOutboxClaimOwnerId::fromString($owner), $this->at($claimed), $this->at($expires));
    }

    private function at(int $minute): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-19T10:00:00+00:00')->modify("+{$minute} minutes");
    }

    private function countRows(string $table): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM {$table}")->fetchColumn();
    }
}
