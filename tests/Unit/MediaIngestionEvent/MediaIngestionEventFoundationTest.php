<?php

namespace Tests\Unit\MediaIngestionEvent;

use App\Application\MediaIngestionEventDelivery\MediaIngestionDeliveryConsumer;
use App\Application\MediaIngestionEventDelivery\MediaIngestionDeliveryOutcome;
use App\Application\MediaIngestionEventDelivery\MediaIngestionReplayRetryPolicy;
use App\Application\MediaIngestionEventDelivery\MediaIngestionRetryDecision;
use App\Application\MediaIngestionEventRouting\DeterministicMediaIngestionEventRouter;
use App\Application\MediaIngestionEventRouting\MediaIngestionRoutingDestination;
use App\Application\MediaIngestionEventTransport\MediaIngestionDeliveryMessageV1;
use App\Application\MediaIngestionEventTransport\MediaIngestionEventTransportException;
use App\Application\MediaIngestionEventTransport\MediaIngestionEventTransportSerializer;
use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventCatalog;
use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventOwner;
use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventType;
use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class MediaIngestionEventFoundationTest extends TestCase
{
    #[Test]
    public function catalog_is_closed_to_three_asset_events(): void
    {
        $catalog = new MediaIngestionEventCatalog;
        self::assertSame(MediaIngestionEventType::cases(), $catalog->events());
        self::assertCount(3, $catalog->events());
        self::assertSame([MediaIngestionEventOwner::Asset], MediaIngestionEventOwner::cases());
    }

    #[Test]
    public function event_and_transport_are_deterministic_and_pii_free(): void
    {
        $first = new MediaIngestionDeliveryMessageV1($this->event(MediaIngestionEventType::AssetReady));
        $second = new MediaIngestionDeliveryMessageV1($this->event(MediaIngestionEventType::AssetReady));
        self::assertSame($first->fields(), $second->fields());
        $serialized = (new MediaIngestionEventTransportSerializer)->serialize($first);
        self::assertStringNotContainsString('filename', $serialized);
        self::assertStringNotContainsString('objectKey', $serialized);
        self::assertStringNotContainsString('url', strtolower($serialized));
    }

    #[Test]
    public function transport_rejects_any_tampering(): void
    {
        $serializer = new MediaIngestionEventTransportSerializer;
        $serialized = $serializer->serialize(new MediaIngestionDeliveryMessageV1($this->event(MediaIngestionEventType::AssetReady)));
        $this->expectException(MediaIngestionEventTransportException::class);
        $serializer->restore(str_replace('"aggregateVersion":1', '"aggregateVersion":2', $serialized));
    }

    #[Test]
    public function routing_matrix_is_closed_and_deterministic(): void
    {
        $router = new DeterministicMediaIngestionEventRouter(new MediaIngestionEventTransportSerializer);
        self::assertSame(
            [MediaIngestionRoutingDestination::PrivateAudit, MediaIngestionRoutingDestination::AuthoringStatus, MediaIngestionRoutingDestination::MediaAttachment],
            $router->route(new MediaIngestionDeliveryMessageV1($this->event(MediaIngestionEventType::AssetReady)))->destinations,
        );
        self::assertSame(
            [MediaIngestionRoutingDestination::PrivateAudit, MediaIngestionRoutingDestination::AuthoringStatus],
            $router->route(new MediaIngestionDeliveryMessageV1($this->event(MediaIngestionEventType::AssetRejected)))->destinations,
        );
        self::assertSame(
            [MediaIngestionRoutingDestination::PrivateAudit, MediaIngestionRoutingDestination::Reconciliation],
            $router->route(new MediaIngestionDeliveryMessageV1($this->event(MediaIngestionEventType::AssetPurged)))->destinations,
        );
    }

    #[Test]
    public function consumer_revalidates_transport_and_destination(): void
    {
        $consumer = new MediaIngestionDeliveryConsumer(new MediaIngestionEventTransportSerializer);
        $message = new MediaIngestionDeliveryMessageV1($this->event(MediaIngestionEventType::AssetReady));
        $fact = $consumer->consume($message, MediaIngestionRoutingDestination::MediaAttachment);
        self::assertSame($message->messageId, $fact->messageId);

        $this->expectException(UnexpectedValueException::class);
        $consumer->consume($message, MediaIngestionRoutingDestination::Reconciliation);
    }

    #[Test]
    public function retry_is_bounded_and_divergence_is_quarantined(): void
    {
        $policy = new MediaIngestionReplayRetryPolicy;
        self::assertSame(MediaIngestionRetryDecision::Complete, $policy->decide(MediaIngestionDeliveryOutcome::Consumed, 1));
        self::assertSame(MediaIngestionRetryDecision::Retry, $policy->decide(MediaIngestionDeliveryOutcome::TransientFailure, 4));
        self::assertSame(MediaIngestionRetryDecision::Quarantine, $policy->decide(MediaIngestionDeliveryOutcome::TransientFailure, 5));
        self::assertSame(MediaIngestionRetryDecision::Quarantine, $policy->decide(MediaIngestionDeliveryOutcome::DivergentReplay, 1));
    }

    private function event(MediaIngestionEventType $type): MediaIngestionEventV1
    {
        return new MediaIngestionEventV1(
            $type,
            '5a000000-0000-4000-8000-000000000001',
            1,
            'media-policy-v1',
            new DateTimeImmutable('2026-07-28T10:00:00.000000+00:00'),
            new DateTimeImmutable('2026-07-28T10:00:01.000000+00:00'),
            '5a000000-0000-4000-8000-000000000002',
            '5a000000-0000-4000-8000-000000000003',
        );
    }
}
