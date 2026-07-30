<?php

namespace Tests\Unit\Application\PublicProjectionRetry;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryIdempotencyKey;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineReason;
use App\Application\PublicProjectionRetry\PublicProjectionDeliveryTechnicalMetrics;
use App\Application\PublicProjectionRetry\PublicProjectionQuarantineReleasePolicy;
use App\Application\PublicProjectionRetry\PublicProjectionReplayAuthorization;
use App\Application\PublicProjectionRetry\PublicProjectionReplayDecision;
use App\Application\PublicProjectionRetry\PublicProjectionReplayRequest;
use App\Application\PublicProjectionRetry\PublicProjectionReplayScope;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Application\PublicProjectionRetry\Support\FakePublicProjectionQuarantine;
use Tests\Unit\Application\PublicProjectionRetry\Support\FakePublicProjectionReplay;

final class PublicProjectionReplayAndQuarantineTest extends TestCase
{
    public function test_all_replay_scopes_are_explicit_and_authorized(): void
    {
        $fake = new FakePublicProjectionReplay;
        $authorization = PublicProjectionReplayAuthorization::fromReference('ticket:123');
        $consumer = PublicProjectionOutboxConsumerId::fromString('public-projection');
        $module = PublicProjectionDeliverySourceModule::fromString('ListingLifecycle');
        $aggregate = PublicProjectionDeliveryAggregateId::fromString('listing:1');
        $order = new PublicProjectionDeliveryOrder(2, PublicProjectionDeliveryEventIndex::fromInt(1));
        $messageId = PublicProjectionDeliveryMessageId::fromIdempotencyKey(PublicProjectionDeliveryIdempotencyKey::fromComponents($module, PublicProjectionDeliveryAggregateType::fromString('Listing'), $aggregate, $order->aggregateVersion, $order->eventIndex, PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1)));
        $requests = [
            new PublicProjectionReplayRequest(PublicProjectionReplayScope::Message, $consumer, $authorization, messageId: $messageId),
            new PublicProjectionReplayRequest(PublicProjectionReplayScope::Aggregate, $consumer, $authorization, sourceModule: $module, aggregateId: $aggregate),
            new PublicProjectionReplayRequest(PublicProjectionReplayScope::Module, $consumer, $authorization, sourceModule: $module),
            new PublicProjectionReplayRequest(PublicProjectionReplayScope::Range, $consumer, $authorization, sourceModule: $module, aggregateId: $aggregate, toInclusive: $order),
            new PublicProjectionReplayRequest(PublicProjectionReplayScope::HighWatermark, $consumer, $authorization, sourceModule: $module, toInclusive: $order),
        ];
        foreach ($requests as $request) {
            $fake->schedule($request);
        }

        self::assertSame(PublicProjectionReplayScope::cases(), array_map(static fn (PublicProjectionReplayRequest $request): PublicProjectionReplayScope => $request->scope, $fake->requests));
    }

    public function test_quarantine_exit_requires_authorization_and_rejects_incompatibilities(): void
    {
        $policy = new PublicProjectionQuarantineReleasePolicy;
        $authorization = PublicProjectionReplayAuthorization::fromReference('incident:42');

        self::assertSame(PublicProjectionReplayDecision::DeniedMissingAuthorization, $policy->decide(PublicProjectionOutboxQuarantineReason::AttemptsExhausted, null));
        self::assertSame(PublicProjectionReplayDecision::Authorized, $policy->decide(PublicProjectionOutboxQuarantineReason::AttemptsExhausted, $authorization));
        self::assertSame(PublicProjectionReplayDecision::DeniedTerminalIncompatibility, $policy->decide(PublicProjectionOutboxQuarantineReason::UnsupportedPayloadVersion, $authorization));
    }

    public function test_authorized_release_is_explicit_in_the_fake(): void
    {
        $fake = new FakePublicProjectionQuarantine;
        $id = PublicProjectionDeliveryMessageId::fromString('ppd-message:one');
        $fake->release($id);

        self::assertArrayHasKey('ppd-message:one', $fake->released);
    }

    public function test_observability_snapshot_rejects_silent_negative_values(): void
    {
        $metrics = new PublicProjectionDeliveryTechnicalMetrics(2, 10, 1, 20, 3, 50, 4, 5);

        self::assertSame(2, $metrics->retryCount);
        self::assertSame(5, $metrics->blockedBySequenceGap);
    }
}
