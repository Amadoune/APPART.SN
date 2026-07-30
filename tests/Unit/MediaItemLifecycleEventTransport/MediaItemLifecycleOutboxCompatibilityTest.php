<?php

namespace Tests\Unit\MediaItemLifecycleEventTransport;

use App\Application\MediaItemLifecycleEventConsumption\MediaItemLifecycleDeliveryConsumer;
use App\Application\MediaItemLifecycleEventConsumption\MediaItemLifecycleDeliveryConsumptionPolicy;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleDeliveryPayload;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRouter;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingDiagnostic;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingResult;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportEnvelope;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryIdempotencyKey;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEvent;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventCatalog;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventId;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventMetadata;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayload;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayloadVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventType;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleOutboxCompatibilityTest extends TestCase
{
    public function test_catalog_accepts_exactly_two_media_item_lifecycle_events(): void
    {
        $catalog = new PublicProjectionDeliveryEventCatalog;
        $payload = new MediaItemLifecycleDeliveryPayload($this->event());

        foreach (MediaItemLifecycleEventType::cases() as $type) {
            self::assertTrue($catalog->accepts(PublicProjectionDeliveryEventType::fromString($type->value), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('Media'), PublicProjectionDeliveryAggregateType::fromString('MediaItemLifecycle'), $payload));
        }
        self::assertCount(2, MediaItemLifecycleEventType::cases());
    }

    #[DataProvider('outcomes')]
    public function test_consumer_routes_once_and_applies_certified_policy(MediaItemLifecycleEventRoutingResult $routing, PublicProjectionDeliveryConsumptionResult $expected): void
    {
        $router = new RecordingMediaItemLifecycleRouter($routing);
        $message = $this->message();

        $result = (new MediaItemLifecycleDeliveryConsumer($router, new MediaItemLifecycleDeliveryConsumptionPolicy))->consume($message);

        self::assertSame($expected, $result);
        self::assertCount(1, $router->envelopes);
        self::assertSame($message->payload->fields(), $router->envelopes[0]->payload->fields());
    }

    public function test_divergent_metadata_never_reaches_router(): void
    {
        $router = new RecordingMediaItemLifecycleRouter(MediaItemLifecycleEventRoutingResult::routed());
        $message = $this->message();
        $divergent = new PublicProjectionDeliveryMessage($message->messageId, $message->idempotencyKey, $message->eventType, $message->payloadVersion, $message->sourceModule, PublicProjectionDeliveryAggregateType::fromString('MediaCollection'), $message->aggregateId, $message->order, $message->occurredAt, $message->recordedAt, $message->payload);

        self::assertSame(PublicProjectionDeliveryConsumptionResult::DivergentPayload, (new MediaItemLifecycleDeliveryConsumer($router, new MediaItemLifecycleDeliveryConsumptionPolicy))->consume($divergent));
        self::assertSame([], $router->envelopes);
    }

    /** @return iterable<string,array{MediaItemLifecycleEventRoutingResult,PublicProjectionDeliveryConsumptionResult}> */
    public static function outcomes(): iterable
    {
        yield 'routed' => [MediaItemLifecycleEventRoutingResult::routed(), PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'deferred' => [MediaItemLifecycleEventRoutingResult::deferred(), PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'retryable' => [MediaItemLifecycleEventRoutingResult::retryableFailure(), PublicProjectionDeliveryConsumptionResult::RetryableFailure];
        yield 'unsupported' => [MediaItemLifecycleEventRoutingResult::rejected(MediaItemLifecycleEventRoutingDiagnostic::UnsupportedEvent), PublicProjectionDeliveryConsumptionResult::UnsupportedEventType];
        yield 'corrupted' => [MediaItemLifecycleEventRoutingResult::rejected(MediaItemLifecycleEventRoutingDiagnostic::CorruptedEvent), PublicProjectionDeliveryConsumptionResult::DivergentPayload];
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $event = $this->event();
        $payload = new MediaItemLifecycleDeliveryPayload($event);
        $source = PublicProjectionDeliverySourceModule::fromString('Media');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('MediaItemLifecycle');
        $id = PublicProjectionDeliveryAggregateId::fromString($event->payload->mediaId->value);
        $type = PublicProjectionDeliveryEventType::fromString($event->metadata->eventType->value);
        $version = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($source, $aggregate, $id, 2, $index, $type, $version);

        return new PublicProjectionDeliveryMessage(PublicProjectionDeliveryMessageId::fromIdempotencyKey($key), $key, $type, $version, $source, $aggregate, $id, new PublicProjectionDeliveryOrder(2, $index), new DateTimeImmutable('2026-07-23T12:00:00Z'), new DateTimeImmutable('2026-07-23T12:00:01Z'), $payload);
    }

    private function event(): MediaItemLifecycleEvent
    {
        $transition = new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Removed, MediaItemLifecycleAction::Remove);
        $id = MediaItemLifecycleId::fromString('a4100000-0000-4000-8000-000000000099');
        $version = MediaItemLifecycleEventPayloadVersion::V1;
        $type = (new MediaItemLifecycleEventCatalog)->typeFor($transition);
        $eventId = MediaItemLifecycleEventId::derive($type, $version, $id, $transition, 2);
        $at = MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T12:00:00Z'));

        return new MediaItemLifecycleEvent(new MediaItemLifecycleEventMetadata($type, $version, MediaItemLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at), new MediaItemLifecycleEventPayload($eventId, $id, 'active>remove>removed', $transition->from, $transition->to, $transition->action, 1, 2));
    }
}

final class RecordingMediaItemLifecycleRouter implements MediaItemLifecycleEventRouter
{
    /** @var list<MediaItemLifecycleTransportEnvelope> */
    public array $envelopes = [];

    public function __construct(private readonly MediaItemLifecycleEventRoutingResult $result) {}

    public function route(MediaItemLifecycleTransportEnvelope $envelope): MediaItemLifecycleEventRoutingResult
    {
        $this->envelopes[] = $envelope;

        return $this->result;
    }
}
