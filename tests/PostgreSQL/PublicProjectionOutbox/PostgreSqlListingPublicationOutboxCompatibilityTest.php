<?php

namespace Tests\PostgreSQL\PublicProjectionOutbox;

use App\Application\ListingPublicationEventTransport\ListingPublicationDeliveryPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxReader;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlListingPublicationOutboxCompatibilityTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_mapper_round_trip_preserves_both_identities_checksum_and_canonical_event(): void
    {
        $event = (new ListingPublicationEventCatalog)->eventsFor(
            ListingId::fromString('11111111-1111-4111-8111-111111111111'),
            new ListingPublicationTransition(ListingPublicationState::Draft, ListingPublicationState::Submitted, ListingPublicationAction::Submit),
            2,
            new ListingPublicationEventMetadata(
                ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:00.000000Z'),
                ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:01.000000Z'),
            ),
        )[0];
        $payload = new ListingPublicationDeliveryPayload($event);
        $fact = new PublicProjectionDeliveryPublishableFact(PublicProjectionDeliveryEventType::fromString($event->type->value), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'), PublicProjectionDeliveryAggregateType::fromString('Listing'), PublicProjectionDeliveryAggregateId::fromString($event->payload->listingId->value), new PublicProjectionDeliveryOrder(2, PublicProjectionDeliveryEventIndex::fromInt(1)), new DateTimeImmutable($event->metadata->occurredAt->value), $payload);
        $message = (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable($event->metadata->recordedAt->value));
        $consumer = PublicProjectionOutboxConsumerId::fromString('listing-publication-router');
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;
        (new PostgreSqlPublicProjectionOutboxWriter($this->connection, $mapper))->append($message, $consumer);
        $records = (new PostgreSqlPublicProjectionOutboxReader($this->connection, $mapper))->findClaimable($consumer, 10);

        self::assertCount(1, $records);
        $restored = $records[0]->message;
        self::assertSame($message->messageId->value, $restored->messageId->value);
        self::assertInstanceOf(ListingPublicationDeliveryPayload::class, $restored->payload);
        self::assertSame($event->eventId->value, $restored->payload->event->eventId->value);
        self::assertSame($payload->fields(), $restored->payload->fields());
        self::assertSame($payload->checksum(), $restored->payload->checksum());
        self::assertEquals($message->occurredAt, $restored->occurredAt);
        self::assertEquals($message->recordedAt, $restored->recordedAt);
    }
}
