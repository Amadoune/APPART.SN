<?php

namespace Tests\PostgreSQL\PublicProjectionOutbox;

use App\Application\MultiTargetDelivery\MultiTargetPropagationRequest;
use App\Application\MultiTargetDelivery\MultiTargetPropagationSource;
use App\Application\MultiTargetDelivery\PagedMultiTargetPropagationStrategy;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryPropertyPayload;
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
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateOutcome;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolution;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolutionStatus;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolver;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionUpdaterConsumer;
use App\Infrastructure\PropertyListingResolution\PostgreSql\PostgreSqlPropertyListingsResolver;
use App\Infrastructure\PropertyListingResolution\PropertyListingsCheckpoint;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Application\PublicProjectionUpdaterIntegration\Support\FakePublicProjectionSourceLookup;
use Tests\Unit\Application\PublicProjectionUpdaterIntegration\Support\FakePublicProjectionUpdateExecutor;

final class PostgreSqlPublicProjectionMultiTargetConsumerTest extends TestCase
{
    private const string PROPERTY = '9b000000-0000-4000-8000-000000000001';

    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_property_targets_are_read_from_postgresql_across_pages_before_terminal_success(): void
    {
        foreach (['9a000000-0000-4000-8000-000000000003', '9a000000-0000-4000-8000-000000000001', '9a000000-0000-4000-8000-000000000002'] as $id) {
            $statement = $this->connection->prepare("INSERT INTO listing_lifecycle.listings(id,property_id,status,last_changed_at,last_changed_at_offset,version) VALUES (:id,:property,'draft','2026-07-19T10:00:00+00:00',0,0)");
            $statement->execute(['id' => $id, 'property' => self::PROPERTY]);
        }
        $request = new MultiTargetPropagationRequest(MultiTargetPropagationSource::Property, self::PROPERTY, self::PROPERTY);
        $resolution = new PublicProjectionSourceResolution(PublicProjectionSourceResolutionStatus::MultiTargetResolved, multiTargetRequest: $request);
        $updater = new FakePublicProjectionUpdateExecutor(PublicListingProjectionUpdateOutcome::Applied);
        $consumer = new PublicProjectionUpdaterConsumer(
            new PublicProjectionSourceResolver(new PublicProjectionDeliveryEventCatalog, new FakePublicProjectionSourceLookup($resolution)),
            $updater,
            new PagedMultiTargetPropagationStrategy(new PostgreSqlPropertyListingsResolver($this->connection, new PropertyListingsCheckpoint)),
            2,
        );

        self::assertSame(PublicProjectionDeliveryConsumptionResult::Consumed, $consumer->consume($this->message()));
        self::assertSame(3, $updater->calls);
        self::assertSame(3, (int) $this->connection->query('SELECT COUNT(*) FROM listing_lifecycle.listings')->fetchColumn());
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $fact = new PublicProjectionDeliveryPublishableFact(PublicProjectionDeliveryEventType::fromString('property.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('RealEstateCatalog'), PublicProjectionDeliveryAggregateType::fromString('Property'), PublicProjectionDeliveryAggregateId::fromString(self::PROPERTY), new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)), new DateTimeImmutable('2026-07-19T10:00:00+00:00'), new PublicProjectionDeliveryPropertyPayload(self::PROPERTY));

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable('2026-07-19T10:01:00+00:00'));
    }
}
