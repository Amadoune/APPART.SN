<?php

namespace Tests\PostgreSQL\PublicProjectionOutbox;

use App\Application\LeadLifecycleEventTransport\LeadLifecycleDeliveryPayload;
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
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEvent;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventMetadata;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayload;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayloadVersion;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventType;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlLeadLifecycleOutboxCompatibilityTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_outbox_round_trip_preserves_lead_event_and_transport_byte_for_byte(): void
    {
        $message = $this->message();
        $consumer = PublicProjectionOutboxConsumerId::fromString('lead-compatibility-test');
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;
        (new PostgreSqlPublicProjectionOutboxWriter($this->connection, $mapper))->append($message, $consumer);
        $records = (new PostgreSqlPublicProjectionOutboxReader($this->connection, $mapper))->findClaimable($consumer, 10);

        self::assertCount(1, $records);
        self::assertInstanceOf(LeadLifecycleDeliveryPayload::class, $records[0]->message->payload);
        self::assertSame($message->payload->fields()['canonicalEvent'], $records[0]->message->payload->fields()['canonicalEvent']);
        self::assertSame($message->payload->checksum(), $records[0]->message->payload->checksum());
        self::assertSame($message->payload->event->payload->eventId->value, $records[0]->message->payload->event->payload->eventId->value);
        self::assertSame('ContactsLeads', $records[0]->message->sourceModule->value);
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $transition = new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver);
        $leadId = LeadId::fromString('a4100000-0000-4000-8000-000000000098');
        $eventVersion = LeadLifecycleEventPayloadVersion::V1;
        $eventId = LeadLifecycleEventId::derive(LeadLifecycleEventType::Delivered, $eventVersion, $leadId, $transition, 2);
        $at = LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00Z'));
        $event = new LeadLifecycleEvent(
            new LeadLifecycleEventMetadata(LeadLifecycleEventType::Delivered, $eventVersion, LeadLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at),
            new LeadLifecycleEventPayload($eventId, $leadId, 'created>deliver>delivered', $transition->from, $transition->to, $transition->action, 1, 2),
        );
        $payload = new LeadLifecycleDeliveryPayload($event);
        $source = PublicProjectionDeliverySourceModule::fromString('ContactsLeads');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('LeadLifecycle');
        $identity = PublicProjectionDeliveryAggregateId::fromString($leadId->value);
        $type = PublicProjectionDeliveryEventType::fromString(LeadLifecycleEventType::Delivered->value);
        $version = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($source, $aggregate, $identity, 2, $index, $type, $version);

        return new PublicProjectionDeliveryMessage(PublicProjectionDeliveryMessageId::fromIdempotencyKey($key), $key, $type, $version, $source, $aggregate, $identity, new PublicProjectionDeliveryOrder(2, $index), new DateTimeImmutable('2026-07-22T12:00:00Z'), new DateTimeImmutable('2026-07-22T12:00:01Z'), $payload);
    }
}
