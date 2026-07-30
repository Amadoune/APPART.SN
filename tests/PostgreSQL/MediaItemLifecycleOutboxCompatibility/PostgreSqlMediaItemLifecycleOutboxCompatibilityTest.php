<?php

namespace Tests\PostgreSQL\MediaItemLifecycleOutboxCompatibility;

use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleDeliveryPayload;
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
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEvent;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventId;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventMetadata;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayload;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayloadVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventType;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlMediaItemLifecycleOutboxCompatibilityTest extends TestCase
{
    public function test_payload_round_trips_byte_for_byte_through_media_owner(): void
    {
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $message = $this->message();
        $consumer = PublicProjectionOutboxConsumerId::fromString('media-item-lifecycle-compatibility');
        $mapper = new PostgreSqlPublicProjectionOutboxMapper;

        (new PostgreSqlPublicProjectionOutboxWriter($connection, $mapper))->append($message, $consumer);
        $records = (new PostgreSqlPublicProjectionOutboxReader($connection, $mapper))->findClaimable($consumer, 10);

        self::assertCount(1, $records);
        self::assertInstanceOf(MediaItemLifecycleDeliveryPayload::class, $records[0]->message->payload);
        self::assertSame($message->payload->fields(), $records[0]->message->payload->fields());
        self::assertSame($message->payload->checksum(), $records[0]->message->payload->checksum());
        self::assertSame($message->messageId->value, $records[0]->message->messageId->value);
        self::assertSame('Media', $records[0]->message->sourceModule->value);
        self::assertSame(1, (int) $connection->query('SELECT count(*) FROM media.public_projection_outbox_messages')->fetchColumn());
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $transition = new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Removed, MediaItemLifecycleAction::Remove);
        $mediaId = MediaItemLifecycleId::fromString('a4100000-0000-4000-8000-000000000099');
        $version = MediaItemLifecycleEventPayloadVersion::V1;
        $eventId = MediaItemLifecycleEventId::derive(MediaItemLifecycleEventType::Removed, $version, $mediaId, $transition, 2);
        $occurred = MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T12:00:00Z'));
        $recorded = MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T12:00:01Z'));
        $event = new MediaItemLifecycleEvent(new MediaItemLifecycleEventMetadata(MediaItemLifecycleEventType::Removed, $version, MediaItemLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $occurred, $recorded), new MediaItemLifecycleEventPayload($eventId, $mediaId, 'active>remove>removed', $transition->from, $transition->to, $transition->action, 1, 2));
        $payload = new MediaItemLifecycleDeliveryPayload($event);
        $source = PublicProjectionDeliverySourceModule::fromString('Media');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('MediaItemLifecycle');
        $id = PublicProjectionDeliveryAggregateId::fromString($mediaId->value);
        $type = PublicProjectionDeliveryEventType::fromString(MediaItemLifecycleEventType::Removed->value);
        $payloadVersion = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($source, $aggregate, $id, 2, $index, $type, $payloadVersion);

        return new PublicProjectionDeliveryMessage(PublicProjectionDeliveryMessageId::fromIdempotencyKey($key), $key, $type, $payloadVersion, $source, $aggregate, $id, new PublicProjectionDeliveryOrder(2, $index), $occurred->value, $recorded->value, $payload);
    }
}
