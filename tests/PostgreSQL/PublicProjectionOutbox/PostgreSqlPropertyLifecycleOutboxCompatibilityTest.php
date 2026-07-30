<?php

namespace Tests\PostgreSQL\PublicProjectionOutbox;

use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleDeliveryPayload;
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
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventCatalog;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventInstant;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventMetadata;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPropertyLifecycleOutboxCompatibilityTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_mapper_round_trip_preserves_canonical_event_checksum_and_separate_identities(): void
    {
        $event = (new PropertyLifecycleEventCatalog)->eventsFor(
            PropertyId::fromString('22222222-2222-4222-8222-222222222222'),
            new PropertyLifecycleTransition(PropertyLifecycleState::Draft, PropertyLifecycleState::Active, PropertyLifecycleAction::Activate),
            2,
            new PropertyLifecycleEventMetadata(PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:00.000000Z'), PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:01.000000Z')),
        )[0];
        $payload = new PropertyLifecycleDeliveryPayload($event);
        $fact = new PublicProjectionDeliveryPublishableFact(PublicProjectionDeliveryEventType::fromString($event->type->value), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('RealEstateCatalog'), PublicProjectionDeliveryAggregateType::fromString('Property'), PublicProjectionDeliveryAggregateId::fromString($event->payload->propertyId->value), new PublicProjectionDeliveryOrder(2, PublicProjectionDeliveryEventIndex::fromInt(1)), new DateTimeImmutable($event->metadata->occurredAt->value), $payload);
        $message = (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable($event->metadata->recordedAt->value));
        $consumer = PublicProjectionOutboxConsumerId::fromString('property-lifecycle-router');
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;
        (new PostgreSqlPublicProjectionOutboxWriter($this->connection, $mapper))->append($message, $consumer);
        $restored = (new PostgreSqlPublicProjectionOutboxReader($this->connection, $mapper))->findClaimable($consumer, 10)[0]->message;

        self::assertSame($message->messageId->value, $restored->messageId->value);
        self::assertInstanceOf(PropertyLifecycleDeliveryPayload::class, $restored->payload);
        self::assertNotSame($event->eventId->value, $restored->messageId->value);
        self::assertSame($event->eventId->value, $restored->payload->event->eventId->value);
        self::assertSame($payload->fields(), $restored->payload->fields());
        self::assertSame($payload->checksum(), $restored->payload->checksum());
        self::assertEquals($message->occurredAt, $restored->occurredAt);
        self::assertEquals($message->recordedAt, $restored->recordedAt);
    }
}
