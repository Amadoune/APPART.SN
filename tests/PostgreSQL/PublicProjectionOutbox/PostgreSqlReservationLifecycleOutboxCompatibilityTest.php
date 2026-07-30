<?php

namespace Tests\PostgreSQL\PublicProjectionOutbox;

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
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleDeliveryPayload;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportEnvelope;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxReader;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventCatalog;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventType;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlReservationLifecycleOutboxCompatibilityTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_all_eleven_events_round_trip_without_transport_loss(): void
    {
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;
        $writer = new PostgreSqlPublicProjectionOutboxWriter($this->connection, $mapper);
        $consumer = PublicProjectionOutboxConsumerId::fromString('reservation-compatibility');
        $expected = [];

        foreach (ReservationLifecycleEventType::cases() as $offset => $type) {
            $message = $this->message($type, $offset + 1);
            $expected[$message->messageId->value] = $message;
            $writer->append($message, $consumer);
        }

        $records = (new PostgreSqlPublicProjectionOutboxReader($this->connection, $mapper))->findClaimable($consumer, 20);
        self::assertCount(11, $records);
        foreach ($records as $record) {
            self::assertInstanceOf(ReservationLifecycleDeliveryPayload::class, $record->message->payload);
            $envelope = ReservationLifecycleTransportEnvelope::wrap($record->message->payload);
            self::assertSame($record->message->payload->fields()['canonicalEvent'], $envelope->payload->fields()['canonicalEvent']);
            self::assertSame($record->message->payload->checksum(), $envelope->metadata->payloadChecksum);
            self::assertSame($record->message->payload->event->payload->eventId->value, $envelope->metadata->businessEventId);
            self::assertArrayHasKey($record->message->messageId->value, $expected);
            self::assertEquals($expected[$record->message->messageId->value], $record->message);
        }
    }

    private function message(ReservationLifecycleEventType $type, int $version): PublicProjectionDeliveryMessage
    {
        $transition = match ($type) {
            ReservationLifecycleEventType::ReservationSubmitted => [ReservationLifecycleState::Draft, ReservationLifecycleAction::Submit, ReservationLifecycleState::Requested],
            ReservationLifecycleEventType::ReservationCancelledFromDraft => [ReservationLifecycleState::Draft, ReservationLifecycleAction::Cancel, ReservationLifecycleState::Cancelled],
            ReservationLifecycleEventType::ReservationConfirmed => [ReservationLifecycleState::Requested, ReservationLifecycleAction::Confirm, ReservationLifecycleState::Confirmed],
            ReservationLifecycleEventType::ReservationRejected => [ReservationLifecycleState::Requested, ReservationLifecycleAction::Reject, ReservationLifecycleState::Rejected],
            ReservationLifecycleEventType::ReservationCancelledFromRequested => [ReservationLifecycleState::Requested, ReservationLifecycleAction::Cancel, ReservationLifecycleState::Cancelled],
            ReservationLifecycleEventType::ReservationExpiredFromRequested => [ReservationLifecycleState::Requested, ReservationLifecycleAction::Expire, ReservationLifecycleState::Expired],
            ReservationLifecycleEventType::ReservationStarted => [ReservationLifecycleState::Confirmed, ReservationLifecycleAction::Start, ReservationLifecycleState::InProgress],
            ReservationLifecycleEventType::ReservationCancelledFromConfirmed => [ReservationLifecycleState::Confirmed, ReservationLifecycleAction::Cancel, ReservationLifecycleState::Cancelled],
            ReservationLifecycleEventType::ReservationExpiredFromConfirmed => [ReservationLifecycleState::Confirmed, ReservationLifecycleAction::Expire, ReservationLifecycleState::Expired],
            ReservationLifecycleEventType::ReservationCompleted => [ReservationLifecycleState::InProgress, ReservationLifecycleAction::Complete, ReservationLifecycleState::Completed],
            ReservationLifecycleEventType::ReservationCancelledInProgress => [ReservationLifecycleState::InProgress, ReservationLifecycleAction::Cancel, ReservationLifecycleState::Cancelled],
        };
        $event = (new ReservationLifecycleEventCatalog)->eventFor(ReservationId::fromString(sprintf('22222222-2222-4222-8222-%012d', $version)), new ReservationLifecycleTransition($transition[0], $transition[2], $transition[1]), $version);
        $payload = new ReservationLifecycleDeliveryPayload($event);
        $module = PublicProjectionDeliverySourceModule::fromString('ReservationLifecycle');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('ReservationLifecycle');
        $id = PublicProjectionDeliveryAggregateId::fromString($event->payload->reservationId->value);
        $eventType = PublicProjectionDeliveryEventType::fromString($type->value);
        $payloadVersion = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($module, $aggregate, $id, $version, $index, $eventType, $payloadVersion);

        return new PublicProjectionDeliveryMessage(PublicProjectionDeliveryMessageId::fromIdempotencyKey($key), $key, $eventType, $payloadVersion, $module, $aggregate, $id, new PublicProjectionDeliveryOrder($version, $index), new DateTimeImmutable('2026-07-21T10:00:00Z'), new DateTimeImmutable('2026-07-21T10:00:01Z'), $payload);
    }
}
