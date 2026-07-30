<?php

namespace Tests\Unit\Contracts\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryStatus;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimOwnerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimResult;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimState;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxCursor;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxCursorIdentity;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxLease;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineReason;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRecord;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxReplayRequest;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryBackoff;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryClassification;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxClaimManager;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxCursorStore;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxReader;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxState;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxWriter;

abstract class PublicProjectionOutboxContract extends TestCase
{
    public function test_record_is_immutable_and_starts_pending_without_attempt_or_lease(): void
    {
        $record = PublicProjectionOutboxRecord::pending($this->message(), $this->consumer());

        self::assertTrue((new ReflectionClass($record))->isReadOnly());
        self::assertSame(PublicProjectionDeliveryStatus::Pending, $record->status);
        self::assertSame(0, $record->attempts->value);
        self::assertSame(PublicProjectionOutboxClaimState::Unclaimed, $record->claimState);
        self::assertNull($record->lease);
    }

    public function test_append_is_idempotent_and_detects_divergent_payload(): void
    {
        [$state, $writer] = $this->writer();
        $message = $this->message();

        self::assertSame(PublicProjectionOutboxWriteResult::Applied, $writer->append($message, $this->consumer()));
        self::assertSame(PublicProjectionOutboxWriteResult::AlreadyApplied, $writer->append($message, $this->consumer()));
        self::assertCount(1, $state->records);

        $payload = new PublicProjectionDeliveryListingPayload('listing:other');
        $divergent = new PublicProjectionDeliveryMessage($message->messageId, $message->idempotencyKey, $message->eventType, $message->payloadVersion, $message->sourceModule, $message->aggregateType, $message->aggregateId, $message->order, $message->occurredAt, $message->recordedAt, $payload);
        self::assertSame(PublicProjectionOutboxWriteResult::DivergentMessage, $writer->append($divergent, $this->consumer()));
    }

    public function test_claim_already_claimed_expiration_and_release_are_explicit(): void
    {
        [$state, $writer] = $this->writer();
        $message = $this->message();
        $writer->append($message, $this->consumer());
        $claims = new FakePublicProjectionOutboxClaimManager($state);
        $first = $this->lease('relay:a', 1, 5);
        $second = $this->lease('relay:b', 2, 6);

        self::assertSame(PublicProjectionOutboxClaimResult::Claimed, $claims->claim($message, $this->consumer(), $first));
        self::assertSame(PublicProjectionOutboxClaimResult::AlreadyClaimed, $claims->claim($message, $this->consumer(), $second));
        self::assertSame(PublicProjectionOutboxClaimResult::LeaseExpired, $claims->expire($message, $this->consumer(), $this->at(5)));
        self::assertSame(PublicProjectionOutboxClaimResult::Claimed, $claims->claim($message, $this->consumer(), $second));
        self::assertSame(PublicProjectionOutboxWriteResult::Applied, $writer->releaseClaim($message, $this->consumer(), $second->ownerId));
    }

    public function test_expired_lease_can_be_reclaimed_without_losing_attempts(): void
    {
        [$state, $writer] = $this->writer();
        $message = $this->message();
        $writer->append($message, $this->consumer());
        $claims = new FakePublicProjectionOutboxClaimManager($state);
        $claims->claim($message, $this->consumer(), $this->lease('relay:a', 1, 2));

        self::assertSame(PublicProjectionOutboxClaimResult::LeaseExpired, $claims->claim($message, $this->consumer(), $this->lease('relay:b', 3, 6)));
        self::assertSame(2, $state->find($message, $this->consumer())?->attempts->value);
    }

    public function test_retry_and_both_blocking_states_are_distinct(): void
    {
        foreach ([
            [PublicProjectionOutboxRetryClassification::Transient, PublicProjectionDeliveryStatus::RetryScheduled],
            [PublicProjectionOutboxRetryClassification::SourceNotReady, PublicProjectionDeliveryStatus::BlockedBySourceReadiness],
            [PublicProjectionOutboxRetryClassification::SequenceGap, PublicProjectionDeliveryStatus::BlockedBySequenceGap],
        ] as [$classification, $expected]) {
            [$state, $writer] = $this->writer();
            $message = $this->message();
            $writer->append($message, $this->consumer());
            $lease = $this->lease('relay:a', 1, 5);
            (new FakePublicProjectionOutboxClaimManager($state))->claim($message, $this->consumer(), $lease);
            $decision = new PublicProjectionOutboxRetryDecision($classification, new PublicProjectionOutboxRetryBackoff(30), true);

            self::assertSame(PublicProjectionOutboxWriteResult::Applied, $writer->scheduleRetry($message, $this->consumer(), $lease->ownerId, $decision));
            self::assertSame($expected, $state->find($message, $this->consumer())?->status);
        }
    }

    public function test_quarantine_is_terminal_and_keeps_reason_and_attempt_count(): void
    {
        [$state, $writer] = $this->writer();
        $message = $this->message();
        $writer->append($message, $this->consumer());
        $decision = new PublicProjectionOutboxQuarantineDecision(PublicProjectionOutboxQuarantineReason::DivergentPayload, 'payload_diverged');

        self::assertSame(PublicProjectionOutboxWriteResult::Applied, $writer->quarantine($message, $this->consumer(), null, $decision));
        $record = $state->find($message, $this->consumer());
        self::assertSame(PublicProjectionDeliveryStatus::Quarantined, $record?->status);
        self::assertSame(PublicProjectionOutboxQuarantineReason::DivergentPayload, $record?->quarantine?->decision->reason);
        self::assertSame(0, $record?->quarantine?->attempts->value);
    }

    public function test_reader_exposes_claimable_retry_blocked_quarantined_and_aggregate_queries(): void
    {
        [$state, $writer] = $this->writer();
        $message = $this->message();
        $writer->append($message, $this->consumer());
        $reader = new FakePublicProjectionOutboxReader($state);

        self::assertCount(1, $reader->findClaimable($this->consumer(), 10));
        self::assertCount(1, $reader->findByAggregate($this->consumer(), $message->aggregateId));
        self::assertSame([], $reader->findRetryable($this->consumer()));
        self::assertSame([], $reader->findBlocked($this->consumer()));
        self::assertSame([], $reader->findQuarantined($this->consumer()));
    }

    public function test_nothing_to_claim_and_claim_mismatch_are_explicit(): void
    {
        $state = new FakePublicProjectionOutboxState;
        $message = $this->message();
        $claims = new FakePublicProjectionOutboxClaimManager($state);

        self::assertSame(PublicProjectionOutboxClaimResult::NothingToClaim, $claims->claim($message, $this->consumer(), $this->lease('relay:a', 1, 5)));
        [$state, $writer] = $this->writer();
        $writer->append($message, $this->consumer());
        (new FakePublicProjectionOutboxClaimManager($state))->claim($message, $this->consumer(), $this->lease('relay:a', 1, 5));
        self::assertSame(PublicProjectionOutboxWriteResult::ClaimMismatch, $writer->markDelivered($message, $this->consumer(), PublicProjectionOutboxClaimOwnerId::fromString('relay:b')));
    }

    public function test_cursor_tracks_progress_high_watermark_and_replay_without_storage_assumption(): void
    {
        $message = $this->message();
        $identity = new PublicProjectionOutboxCursorIdentity($this->consumer(), $message->sourceModule, $message->aggregateType, $message->aggregateId);
        $cursor = new PublicProjectionOutboxCursor($identity, $message->order, new PublicProjectionDeliveryOrder(5, PublicProjectionDeliveryEventIndex::fromInt(1)), $message->messageId);
        $store = new FakePublicProjectionOutboxCursorStore;
        $store->advance($cursor);
        $replay = new PublicProjectionOutboxReplayRequest($identity, $message->order, $cursor->highWatermark);
        $store->beginReplay($replay);

        self::assertEquals($cursor, $store->find($identity));
        self::assertSame($replay, $store->replays[0]);
        self::assertStringContainsString('public-projection', $identity->value());
    }

    public function test_claim_and_writer_contracts_use_only_specialized_intents(): void
    {
        self::assertSame(['append', 'markDelivered', 'scheduleRetry', 'quarantine', 'releaseClaim'], array_map(static fn ($method): string => $method->getName(), (new ReflectionClass(PublicProjectionOutboxWriter::class))->getMethods()));
        self::assertCount(11, PublicProjectionOutboxClaimResult::cases());
        self::assertCount(6, PublicProjectionOutboxQuarantineReason::cases());
    }

    /** @return array{FakePublicProjectionOutboxState, FakePublicProjectionOutboxWriter} */
    private function writer(): array
    {
        $state = new FakePublicProjectionOutboxState;

        return [$state, new FakePublicProjectionOutboxWriter($state)];
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $type = PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested');
        $version = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $module = PublicProjectionDeliverySourceModule::fromString('ListingLifecycle');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('Listing');
        $id = PublicProjectionDeliveryAggregateId::fromString('listing:1');
        $fact = new PublicProjectionDeliveryPublishableFact($type, $version, $module, $aggregate, $id, new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)), $this->at(0), new PublicProjectionDeliveryListingPayload('listing:1'));

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, $this->at(1));
    }

    private function consumer(): PublicProjectionOutboxConsumerId
    {
        return PublicProjectionOutboxConsumerId::fromString('public-projection');
    }

    private function lease(string $owner, int $claimedMinute, int $expiresMinute): PublicProjectionOutboxLease
    {
        return new PublicProjectionOutboxLease(PublicProjectionOutboxClaimOwnerId::fromString($owner), $this->at($claimedMinute), $this->at($expiresMinute));
    }

    private function at(int $minute): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-19T10:00:00+00:00')->modify("+{$minute} minutes");
    }
}
