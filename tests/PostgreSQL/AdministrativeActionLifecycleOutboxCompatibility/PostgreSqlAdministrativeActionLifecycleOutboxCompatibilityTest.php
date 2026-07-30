<?php

namespace Tests\PostgreSQL\AdministrativeActionLifecycleOutboxCompatibility;

use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleDeliveryPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryIdempotencyKey;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxReader;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEvent;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventId;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventMetadata;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayload;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayloadVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventType;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAdministrativeActionLifecycleOutboxCompatibilityTest extends TestCase
{
    public function test_delivery_event_round_trips_byte_for_byte_through_generic_outbox(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $event = $this->event();
        $payload = new AdministrativeActionLifecycleDeliveryPayload($event);
        $source = PublicProjectionDeliverySourceModule::fromString('AdministrationAudit');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('AdministrativeActionLifecycle');
        $aggregateId = PublicProjectionDeliveryAggregateId::fromString($event->payload->actionId->value);
        $eventType = PublicProjectionDeliveryEventType::fromString($event->metadata->eventType->value);
        $version = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($source, $aggregate, $aggregateId, 2, $index, $eventType, $version);
        $message = new PublicProjectionDeliveryMessage(
            PublicProjectionDeliveryMessageId::fromIdempotencyKey($key),
            $key,
            $eventType,
            $version,
            $source,
            $aggregate,
            $aggregateId,
            new PublicProjectionDeliveryOrder(2, $index),
            new DateTimeImmutable('2026-07-24T12:00:00Z'),
            new DateTimeImmutable('2026-07-24T12:00:01Z'),
            $payload,
        );
        $consumer = PublicProjectionOutboxConsumerId::fromString('administrative-action-lifecycle-outbox-compatibility');
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;

        (new PostgreSqlPublicProjectionOutboxWriter($connection, $mapper))->append($message, $consumer);
        $records = (new PostgreSqlPublicProjectionOutboxReader($connection, $mapper))->findClaimable($consumer, 10);

        self::assertCount(1, $records);
        self::assertInstanceOf(AdministrativeActionLifecycleDeliveryPayload::class, $records[0]->message->payload);
        self::assertSame($payload->fields(), $records[0]->message->payload->fields());
        self::assertSame($payload->checksum(), $records[0]->message->payload->checksum());
        self::assertSame($event->payload->eventId->value, $records[0]->message->payload->event->payload->eventId->value);
        self::assertSame(1, (int) $connection->query("SELECT count(*) FROM administration_audit.public_projection_outbox_messages WHERE source_module='AdministrationAudit'")->fetchColumn());
    }

    private function event(): AdministrativeActionLifecycleEvent
    {
        $transition = new AdministrativeActionLifecycleTransition(
            AdministrativeActionLifecycleState::Draft,
            AdministrativeActionLifecycleState::Recorded,
            AdministrativeActionLifecycleAction::Record,
        );
        $id = AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000047');
        $version = AdministrativeActionLifecycleEventPayloadVersion::V1;
        $eventId = AdministrativeActionLifecycleEventId::derive(AdministrativeActionLifecycleEventType::Recorded, $version, $id, $transition, 2);
        $occurred = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T12:00:00Z'));
        $recorded = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T12:00:01Z'));

        return new AdministrativeActionLifecycleEvent(
            new AdministrativeActionLifecycleEventMetadata(AdministrativeActionLifecycleEventType::Recorded, $version, ActorId::fromString('decision-actor-001'), $occurred, $recorded),
            new AdministrativeActionLifecycleEventPayload($eventId, $id, 'draft>record>recorded', $transition->from, $transition->to, $transition->action, 1, 2),
        );
    }
}
