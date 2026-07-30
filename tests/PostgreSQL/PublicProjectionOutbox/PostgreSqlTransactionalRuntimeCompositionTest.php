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
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use DateTimeImmutable;
use PDO;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PostgreSqlTransactionalRuntimeCompositionTest extends TestCase
{
    private const string LISTING = '97000000-0000-4000-8000-000000000001';

    private PDO $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->app->instance(PDO::class, $this->connection);
    }

    public function test_laravel_resolved_repository_and_outbox_commit_atomically_without_nested_transaction(): void
    {
        $this->app->make(PostgreSqlAggregateOutboxTransaction::class)->run(function (): void {
            $this->app->make(ListingRegistry::class)->add($this->listing());
            self::assertSame(PublicProjectionOutboxWriteResult::Applied, $this->app->make(PublicProjectionOutboxWriter::class)->append($this->message(), $this->app->make(PublicProjectionOutboxConsumerId::class)));
        });

        self::assertSame(1, $this->countRows('listing_lifecycle.listings'));
        self::assertSame(1, $this->countRows('listing_lifecycle.public_projection_outbox_messages'));
    }

    public function test_failure_after_aggregate_and_outbox_writes_rolls_both_back(): void
    {
        try {
            $this->app->make(PostgreSqlAggregateOutboxTransaction::class)->run(function (): void {
                $this->app->make(ListingRegistry::class)->add($this->listing());
                $this->app->make(PublicProjectionOutboxWriter::class)->append($this->message(), $this->app->make(PublicProjectionOutboxConsumerId::class));
                throw new RuntimeException('controlled rollback');
            });
            self::fail('The transaction must roll back.');
        } catch (RuntimeException $error) {
            self::assertSame('controlled rollback', $error->getMessage());
        }

        self::assertSame(0, $this->countRows('listing_lifecycle.listings'));
        self::assertSame(0, $this->countRows('listing_lifecycle.public_projection_outbox_messages'));
    }

    public function test_failure_before_outbox_append_rolls_aggregate_back(): void
    {
        try {
            $this->app->make(PostgreSqlAggregateOutboxTransaction::class)->run(function (): void {
                $this->app->make(ListingRegistry::class)->add($this->listing());
                throw new RuntimeException('before append');
            });
            self::fail('The transaction must roll back.');
        } catch (RuntimeException $error) {
            self::assertSame('before append', $error->getMessage());
        }

        self::assertSame(0, $this->countRows('listing_lifecycle.listings'));
        self::assertSame(0, $this->countRows('listing_lifecycle.public_projection_outbox_messages'));
    }

    private function listing(): Listing
    {
        return Listing::createDraft(
            ListingId::fromString(self::LISTING),
            PropertyId::fromString('97000000-0000-4000-8000-000000000002'),
            ListingRevisionId::fromString('97000000-0000-4000-8000-000000000003'),
            new TransitionEvidence(ActorId::fromString('actor:runtime-certification'), TransitionTrigger::DraftStarted, TransitionReason::fromString('Transactional Runtime certification'), TransitionOrigin::Advertiser, new DateTimeImmutable('2026-07-19T10:00:00+00:00')),
            new ListingTransitionPolicy,
            PropertyAvailability::Eligible,
        );
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $at = new DateTimeImmutable('2026-07-19T10:00:00+00:00');
        $fact = new PublicProjectionDeliveryPublishableFact(
            PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested'),
            PublicProjectionDeliveryPayloadVersion::fromInt(1),
            PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'),
            PublicProjectionDeliveryAggregateType::fromString('Listing'),
            PublicProjectionDeliveryAggregateId::fromString(self::LISTING),
            new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)),
            $at,
            new PublicProjectionDeliveryListingPayload(self::LISTING),
        );

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, $at->modify('+1 minute'));
    }

    private function countRows(string $table): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM {$table}")->fetchColumn();
    }
}
