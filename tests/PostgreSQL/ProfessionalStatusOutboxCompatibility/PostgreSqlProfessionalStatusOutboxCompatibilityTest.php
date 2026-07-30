<?php

namespace Tests\PostgreSQL\ProfessionalStatusOutboxCompatibility;

use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusDeliveryPayload;
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
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEvent;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventId;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventMetadata;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayload;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayloadVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventType;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlProfessionalStatusOutboxCompatibilityTest extends TestCase
{
    public function test_payload_round_trips_byte_for_byte_through_professionals_owner(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $message = $this->message();
        $consumer = PublicProjectionOutboxConsumerId::fromString('professional-status-compatibility');
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;
        (new PostgreSqlPublicProjectionOutboxWriter($connection, $mapper))->append($message, $consumer);
        $records = (new PostgreSqlPublicProjectionOutboxReader($connection, $mapper))->findClaimable($consumer, 10);
        self::assertCount(1, $records);
        self::assertInstanceOf(ProfessionalStatusDeliveryPayload::class, $records[0]->message->payload);
        self::assertSame($message->payload->fields(), $records[0]->message->payload->fields());
        self::assertSame($message->payload->checksum(), $records[0]->message->payload->checksum());
        self::assertSame($message->messageId->value, $records[0]->message->messageId->value);
        self::assertSame('Professionals', $records[0]->message->sourceModule->value);
        self::assertSame(1, (int) $connection->query('SELECT count(*) FROM professionals.public_projection_outbox_messages')->fetchColumn());
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $transition = new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Suspended, ProfessionalStatusAction::Suspend);
        $professionalId = ProfessionalStatusId::fromString('a4100000-0000-4000-8000-000000000099');
        $version = ProfessionalStatusEventPayloadVersion::V1;
        $eventId = ProfessionalStatusEventId::derive(ProfessionalStatusEventType::Suspended, $version, $professionalId, $transition, 2);
        $occurred = ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00Z'));
        $recorded = ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:01Z'));
        $event = new ProfessionalStatusEvent(new ProfessionalStatusEventMetadata(ProfessionalStatusEventType::Suspended, $version, ProfessionalStatusActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $occurred, $recorded), new ProfessionalStatusEventPayload($eventId, $professionalId, 'active>suspend>suspended', $transition->from, $transition->to, $transition->action, 1, 2));
        $payload = new ProfessionalStatusDeliveryPayload($event);
        $source = PublicProjectionDeliverySourceModule::fromString('Professionals');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('ProfessionalStatus');
        $id = PublicProjectionDeliveryAggregateId::fromString($professionalId->value);
        $type = PublicProjectionDeliveryEventType::fromString(ProfessionalStatusEventType::Suspended->value);
        $payloadVersion = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($source, $aggregate, $id, 2, $index, $type, $payloadVersion);

        return new PublicProjectionDeliveryMessage(PublicProjectionDeliveryMessageId::fromIdempotencyKey($key), $key, $type, $payloadVersion, $source, $aggregate, $id, new PublicProjectionDeliveryOrder(2, $index), $occurred->value, $recorded->value, $payload);
    }
}
