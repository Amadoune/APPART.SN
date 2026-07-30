<?php

namespace Tests\Unit\Contracts\PublicProjectionDelivery;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogEntry;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCompatibility;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrderRelation;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryStatus;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryTraceId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryUnsupportedFact;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Unit\Contracts\PublicProjectionDelivery\Support\FakePublicProjectionDeliveryConsumer;

abstract class PublicProjectionDeliveryContract extends TestCase
{
    public function test_message_identity_and_idempotency_are_immutable_and_deterministic(): void
    {
        $first = $this->message();
        $second = $this->message(recordedMinute: 20, occurredMinute: 10);

        self::assertTrue((new ReflectionClass($first))->isReadOnly());
        self::assertTrue((new ReflectionClass($first->messageId))->isReadOnly());
        self::assertSame($first->messageId->value, $second->messageId->value);
        self::assertSame($first->idempotencyKey->value, $second->idempotencyKey->value);
        self::assertNotEquals($first->recordedAt, $second->recordedAt);
        self::assertNotEquals($first->occurredAt, $second->occurredAt);
    }

    public function test_every_causal_component_changes_the_key(): void
    {
        $base = $this->message();
        $variants = [
            $this->message(module: 'ListingLifecycleX'),
            $this->message(aggregate: 'ListingX'),
            $this->message(id: 'listing:2'),
            $this->message(version: 2),
            $this->message(index: 2),
            $this->message(type: 'listing.other.requested'),
            $this->message(payloadVersion: 2, customCatalog: true),
        ];

        foreach ($variants as $variant) {
            self::assertNotSame($base->idempotencyKey->value, $variant->idempotencyKey->value);
        }
    }

    public function test_separator_encoding_has_no_collision(): void
    {
        $colon = $this->message(id: 'listing:a:b');
        $encoded = $this->message(id: 'listing:a%3Ab');

        self::assertNotSame($colon->idempotencyKey->value, $encoded->idempotencyKey->value);
        self::assertStringContainsString('%3A', $colon->idempotencyKey->value);
        self::assertStringContainsString('%253A', $encoded->idempotencyKey->value);
    }

    #[DataProvider('orderRelations')]
    public function test_order_relation_is_explicit(int $leftVersion, int $leftIndex, int $rightVersion, int $rightIndex, bool $sameAggregate, PublicProjectionDeliveryOrderRelation $expected): void
    {
        $left = new PublicProjectionDeliveryOrder($leftVersion, PublicProjectionDeliveryEventIndex::fromInt($leftIndex));
        $right = new PublicProjectionDeliveryOrder($rightVersion, PublicProjectionDeliveryEventIndex::fromInt($rightIndex));

        self::assertSame($expected, $left->relationTo($right, $sameAggregate));
    }

    public static function orderRelations(): iterable
    {
        yield 'equal' => [1, 1, 1, 1, true, PublicProjectionDeliveryOrderRelation::Equal];
        yield 'before version' => [1, 1, 2, 1, true, PublicProjectionDeliveryOrderRelation::Before];
        yield 'after version' => [2, 1, 1, 3, true, PublicProjectionDeliveryOrderRelation::After];
        yield 'before index' => [2, 1, 2, 2, true, PublicProjectionDeliveryOrderRelation::Before];
        yield 'after index' => [2, 2, 2, 1, true, PublicProjectionDeliveryOrderRelation::After];
        yield 'version gap' => [4, 1, 1, 1, true, PublicProjectionDeliveryOrderRelation::Gap];
        yield 'index gap' => [2, 4, 2, 1, true, PublicProjectionDeliveryOrderRelation::Gap];
        yield 'different aggregate' => [2, 1, 1, 1, false, PublicProjectionDeliveryOrderRelation::Invalid];
    }

    public function test_catalog_is_closed_and_checks_type_version_source_and_payload(): void
    {
        $catalog = new PublicProjectionDeliveryEventCatalog;
        $message = $this->message();

        self::assertSame(PublicProjectionDeliveryCompatibility::Supported, $catalog->compatibility($message->eventType, $message->payloadVersion));
        self::assertSame(PublicProjectionDeliveryCompatibility::UnsupportedType, $catalog->compatibility(PublicProjectionDeliveryEventType::fromString('unknown.event.type'), $message->payloadVersion));
        self::assertSame(PublicProjectionDeliveryCompatibility::UnsupportedVersion, $catalog->compatibility($message->eventType, PublicProjectionDeliveryPayloadVersion::fromInt(99)));
        self::assertTrue($catalog->accepts($message->eventType, $message->payloadVersion, $message->sourceModule, $message->aggregateType, $message->payload));
        self::assertFalse($catalog->accepts($message->eventType, $message->payloadVersion, PublicProjectionDeliverySourceModule::fromString('ContentSeo'), $message->aggregateType, $message->payload));
    }

    public function test_deprecated_catalog_entry_remains_readable(): void
    {
        $entry = new PublicProjectionDeliveryCatalogEntry(PublicProjectionDeliveryEventType::fromString('listing.legacy.requested'), PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'), PublicProjectionDeliveryAggregateType::fromString('Listing'), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliveryListingPayload::class, true);
        $catalog = new PublicProjectionDeliveryEventCatalog([$entry]);

        self::assertSame(PublicProjectionDeliveryCompatibility::DeprecatedButSupported, $catalog->compatibility($entry->eventType, $entry->payloadVersion));
    }

    public function test_payload_is_typed_immutable_deterministic_and_checksum_detects_divergence(): void
    {
        $payload = new PublicProjectionDeliveryListingPayload('listing:1');
        $same = new PublicProjectionDeliveryListingPayload('listing:1');
        $different = new PublicProjectionDeliveryListingPayload('listing:2');

        self::assertTrue((new ReflectionClass($payload))->isReadOnly());
        self::assertSame(['listingId' => 'listing:1'], $payload->fields());
        self::assertSame($payload->checksum(), $same->checksum());
        self::assertNotSame($payload->checksum(), $different->checksum());

        $first = $this->message();
        $divergent = new PublicProjectionDeliveryMessage($first->messageId, $first->idempotencyKey, $first->eventType, $first->payloadVersion, $first->sourceModule, $first->aggregateType, $first->aggregateId, $first->order, $first->occurredAt, $first->recordedAt, $different);
        self::assertTrue($first->hasDivergentPayload($divergent));
    }

    public function test_envelope_rejects_an_indirectly_mutable_payload(): void
    {
        $first = $this->message();
        $mutable = new class implements PublicProjectionDeliveryPayload
        {
            public string $value = 'mutable';

            public function fields(): array
            {
                return ['value' => $this->value];
            }

            public function checksum(): string
            {
                return 'not-used';
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        new PublicProjectionDeliveryMessage($first->messageId, $first->idempotencyKey, $first->eventType, $first->payloadVersion, $first->sourceModule, $first->aggregateType, $first->aggregateId, $first->order, $first->occurredAt, $first->recordedAt, $mutable);
    }

    public function test_factory_preserves_order_dates_and_trace_metadata(): void
    {
        $message = $this->message(correlation: 'corr:1', causation: 'cause:1');

        self::assertSame(1, $message->order->aggregateVersion);
        self::assertSame(1, $message->order->eventIndex->value);
        self::assertEquals($this->at(1), $message->occurredAt);
        self::assertEquals($this->at(2), $message->recordedAt);
        self::assertSame('corr:1', $message->correlationId?->value);
        self::assertSame('cause:1', $message->causationId?->value);
    }

    public function test_factory_rejects_a_fact_not_explicitly_catalogued(): void
    {
        $this->expectException(PublicProjectionDeliveryUnsupportedFact::class);

        $fact = new PublicProjectionDeliveryPublishableFact(
            PublicProjectionDeliveryEventType::fromString('unknown.event.type'),
            PublicProjectionDeliveryPayloadVersion::fromInt(1),
            PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'),
            PublicProjectionDeliveryAggregateType::fromString('Listing'),
            PublicProjectionDeliveryAggregateId::fromString('listing:1'),
            new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)),
            $this->at(1),
            new PublicProjectionDeliveryListingPayload('listing:1'),
        );
        (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, $this->at(2));
    }

    public function test_fake_consumer_distinguishes_consumed_duplicate_divergence_gap_obsolete_and_readiness(): void
    {
        $catalog = new PublicProjectionDeliveryEventCatalog;
        $consumer = new FakePublicProjectionDeliveryConsumer($catalog);
        $first = $this->message();

        self::assertSame(PublicProjectionDeliveryConsumptionResult::Consumed, $consumer->consume($first));
        self::assertSame(PublicProjectionDeliveryConsumptionResult::AlreadyConsumed, $consumer->consume($first));

        $differentPayload = new PublicProjectionDeliveryListingPayload('listing:divergent');
        $divergent = new PublicProjectionDeliveryMessage($first->messageId, $first->idempotencyKey, $first->eventType, $first->payloadVersion, $first->sourceModule, $first->aggregateType, $first->aggregateId, $first->order, $first->occurredAt, $first->recordedAt, $differentPayload);
        self::assertSame(PublicProjectionDeliveryConsumptionResult::DivergentPayload, $consumer->consume($divergent));
        self::assertSame(PublicProjectionDeliveryConsumptionResult::BlockedBySequenceGap, $consumer->consume($this->message(version: 4)));

        $notReady = new FakePublicProjectionDeliveryConsumer($catalog, false);
        self::assertSame(PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness, $notReady->consume($first));
    }

    public function test_consumer_and_status_results_are_exhaustive_and_non_boolean(): void
    {
        self::assertCount(10, PublicProjectionDeliveryConsumptionResult::cases());
        self::assertCount(7, PublicProjectionDeliveryStatus::cases());
        self::assertTrue(PublicProjectionDeliveryStatus::Pending->canTransitionTo(PublicProjectionDeliveryStatus::Claimed));
        self::assertTrue(PublicProjectionDeliveryStatus::Claimed->canTransitionTo(PublicProjectionDeliveryStatus::RetryScheduled));
        self::assertFalse(PublicProjectionDeliveryStatus::Delivered->canTransitionTo(PublicProjectionDeliveryStatus::Pending));
        self::assertFalse(PublicProjectionDeliveryStatus::Quarantined->canTransitionTo(PublicProjectionDeliveryStatus::Claimed));
    }

    private function message(string $module = 'ListingLifecycle', string $aggregate = 'Listing', string $id = 'listing:1', int $version = 1, int $index = 1, string $type = 'listing.reconstruction.requested', int $payloadVersion = 1, int $occurredMinute = 1, int $recordedMinute = 2, ?string $correlation = null, ?string $causation = null, bool $customCatalog = false): PublicProjectionDeliveryMessage
    {
        $eventType = PublicProjectionDeliveryEventType::fromString($type);
        $sourceModule = PublicProjectionDeliverySourceModule::fromString($module);
        $aggregateType = PublicProjectionDeliveryAggregateType::fromString($aggregate);
        $versionObject = PublicProjectionDeliveryPayloadVersion::fromInt($payloadVersion);
        $payload = new PublicProjectionDeliveryListingPayload($id);
        $entry = new PublicProjectionDeliveryCatalogEntry($eventType, $sourceModule, $aggregateType, $versionObject, PublicProjectionDeliveryListingPayload::class);
        $catalog = $customCatalog || $type !== 'listing.reconstruction.requested' || $module !== 'ListingLifecycle' || $aggregate !== 'Listing' ? new PublicProjectionDeliveryEventCatalog([$entry]) : new PublicProjectionDeliveryEventCatalog;
        $fact = new PublicProjectionDeliveryPublishableFact($eventType, $versionObject, $sourceModule, $aggregateType, PublicProjectionDeliveryAggregateId::fromString($id), new PublicProjectionDeliveryOrder($version, PublicProjectionDeliveryEventIndex::fromInt($index)), $this->at($occurredMinute), $payload, $correlation === null ? null : PublicProjectionDeliveryTraceId::fromString($correlation), $causation === null ? null : PublicProjectionDeliveryTraceId::fromString($causation));

        return (new PublicProjectionDeliveryCatalogMessageFactory($catalog))->create($fact, $this->at($recordedMinute));
    }

    private function at(int $minute): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-19T10:00:00+00:00')->modify("+{$minute} minutes");
    }
}
