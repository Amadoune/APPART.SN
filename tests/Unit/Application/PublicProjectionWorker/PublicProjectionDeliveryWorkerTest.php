<?php

namespace Tests\Unit\Application\PublicProjectionWorker;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryStatus;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistration;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistry;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryOutcome;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorker;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorkerId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Application\PublicProjectionWorker\Support\FakeConfiguredDeliveryConsumer;
use Tests\Unit\Application\PublicProjectionWorker\Support\FakePublicProjectionDeliveryClock;
use Tests\Unit\Application\PublicProjectionWorker\Support\FakePublicProjectionOutboxRetryPolicy;
use Tests\Unit\Contracts\PublicProjectionDelivery\Support\FakePublicProjectionDeliveryConsumer;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxClaimManager;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxReader;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxState;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxWriter;

final class PublicProjectionDeliveryWorkerTest extends TestCase
{
    public function test_empty_and_nominal_bounded_runs_are_explicit(): void
    {
        [$worker, $writer, $state, $consumerId] = $this->harness();
        self::assertSame(0, $worker->runOnce($consumerId)->requested);
        $message = $this->message('a', 1);
        $writer->append($message, $consumerId);

        $result = $worker->runOnce($consumerId);

        self::assertSame(1, $result->requested);
        self::assertSame(1, $result->claimed);
        self::assertSame(1, $result->count(PublicProjectionDeliveryOutcome::Delivered));
        self::assertSame(7140, $result->oldestMessageLagSeconds);
        self::assertSame(PublicProjectionDeliveryStatus::Delivered, $state->find($message, $consumerId)?->status);
    }

    public function test_only_causal_head_is_active_while_distinct_aggregate_progresses(): void
    {
        [$worker, $writer, $state, $consumerId] = $this->harness();
        $first = $this->message('a', 1);
        $later = $this->message('a', 2);
        $parallel = $this->message('b', 1);
        foreach ([$first, $later, $parallel] as $message) {
            $writer->append($message, $consumerId);
        }

        $result = $worker->runOnce($consumerId);

        self::assertSame(2, $result->claimed);
        self::assertSame(1, $result->count(PublicProjectionDeliveryOutcome::DeferredByCausality));
        self::assertSame(PublicProjectionDeliveryStatus::Pending, $state->find($later, $consumerId)?->status);
        self::assertSame(PublicProjectionDeliveryStatus::Delivered, $state->find($parallel, $consumerId)?->status);
    }

    public function test_source_readiness_is_blocked_without_retry_loop(): void
    {
        [$worker, $writer, $state, $consumerId] = $this->harness(sourceReady: false);
        $message = $this->message('a', 1);
        $writer->append($message, $consumerId);

        $result = $worker->runOnce($consumerId);

        self::assertSame(1, $result->count(PublicProjectionDeliveryOutcome::BlockedBySourceReadiness));
        self::assertSame(PublicProjectionDeliveryStatus::BlockedBySourceReadiness, $state->find($message, $consumerId)?->status);
        self::assertSame(0, $worker->runOnce($consumerId)->requested);
    }

    #[DataProvider('terminalResultProvider')]
    public function test_every_terminal_consumer_result_has_an_explicit_outcome(PublicProjectionDeliveryConsumptionResult $consumption, PublicProjectionDeliveryOutcome $outcome, PublicProjectionDeliveryStatus $status): void
    {
        [$worker, $writer, $state, $consumerId] = $this->harness(consumer: new FakeConfiguredDeliveryConsumer($consumption));
        $message = $this->message('c', 1);
        $writer->append($message, $consumerId);

        $result = $worker->runOnce($consumerId);

        self::assertSame(1, $result->count($outcome));
        self::assertSame($status, $state->find($message, $consumerId)?->status);
    }

    public static function terminalResultProvider(): iterable
    {
        yield 'consumed' => [PublicProjectionDeliveryConsumptionResult::Consumed, PublicProjectionDeliveryOutcome::Delivered, PublicProjectionDeliveryStatus::Delivered];
        yield 'already consumed' => [PublicProjectionDeliveryConsumptionResult::AlreadyConsumed, PublicProjectionDeliveryOutcome::AlreadyConsumed, PublicProjectionDeliveryStatus::Delivered];
        yield 'obsolete' => [PublicProjectionDeliveryConsumptionResult::RejectedObsolete, PublicProjectionDeliveryOutcome::Obsolete, PublicProjectionDeliveryStatus::Delivered];
        yield 'sequence gap' => [PublicProjectionDeliveryConsumptionResult::BlockedBySequenceGap, PublicProjectionDeliveryOutcome::BlockedBySequenceGap, PublicProjectionDeliveryStatus::BlockedBySequenceGap];
        yield 'source readiness' => [PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness, PublicProjectionDeliveryOutcome::BlockedBySourceReadiness, PublicProjectionDeliveryStatus::BlockedBySourceReadiness];
        yield 'unsupported event' => [PublicProjectionDeliveryConsumptionResult::UnsupportedEventType, PublicProjectionDeliveryOutcome::Unsupported, PublicProjectionDeliveryStatus::Quarantined];
        yield 'unsupported version' => [PublicProjectionDeliveryConsumptionResult::UnsupportedPayloadVersion, PublicProjectionDeliveryOutcome::Unsupported, PublicProjectionDeliveryStatus::Quarantined];
        yield 'divergent' => [PublicProjectionDeliveryConsumptionResult::DivergentPayload, PublicProjectionDeliveryOutcome::Quarantined, PublicProjectionDeliveryStatus::Quarantined];
        yield 'retryable' => [PublicProjectionDeliveryConsumptionResult::RetryableFailure, PublicProjectionDeliveryOutcome::RetryScheduled, PublicProjectionDeliveryStatus::RetryScheduled];
        yield 'permanent' => [PublicProjectionDeliveryConsumptionResult::PermanentFailure, PublicProjectionDeliveryOutcome::Quarantined, PublicProjectionDeliveryStatus::Quarantined];
    }

    /** @return array{PublicProjectionDeliveryWorker, FakePublicProjectionOutboxWriter, FakePublicProjectionOutboxState, PublicProjectionOutboxConsumerId} */
    private function harness(bool $sourceReady = true, ?PublicProjectionDeliveryConsumer $consumer = null): array
    {
        $state = new FakePublicProjectionOutboxState;
        $writer = new FakePublicProjectionOutboxWriter($state);
        $catalog = new PublicProjectionDeliveryEventCatalog;
        $consumer ??= new FakePublicProjectionDeliveryConsumer($catalog, $sourceReady);
        $consumerId = PublicProjectionOutboxConsumerId::fromString('public-projection');
        $event = PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested');
        $registry = new PublicProjectionDeliveryConsumerRegistry([
            new PublicProjectionDeliveryConsumerRegistration($consumerId, $event, PublicProjectionDeliveryPayloadVersion::fromInt(1), $consumer),
        ]);
        $worker = new PublicProjectionDeliveryWorker(new FakePublicProjectionOutboxReader($state), new FakePublicProjectionOutboxClaimManager($state), $writer, $registry, new FakePublicProjectionOutboxRetryPolicy, new FakePublicProjectionDeliveryClock(new DateTimeImmutable('2026-07-19T12:00:00+00:00')), PublicProjectionDeliveryWorkerId::fromString('worker:a'), 10, 60);

        return [$worker, $writer, $state, $consumerId];
    }

    private function message(string $id, int $version): PublicProjectionDeliveryMessage
    {
        $aggregateId = str_pad($id, 36, '0');
        $fact = new PublicProjectionDeliveryPublishableFact(
            PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested'),
            PublicProjectionDeliveryPayloadVersion::fromInt(1),
            PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'),
            PublicProjectionDeliveryAggregateType::fromString('Listing'),
            PublicProjectionDeliveryAggregateId::fromString($aggregateId),
            new PublicProjectionDeliveryOrder($version, PublicProjectionDeliveryEventIndex::fromInt(1)),
            new DateTimeImmutable('2026-07-19T10:00:00+00:00'),
            new PublicProjectionDeliveryListingPayload($aggregateId),
        );

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable('2026-07-19T10:01:00+00:00'));
    }
}
