<?php

namespace Tests\Unit\Contracts\PublicProjectionOutbox\Support;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryStatus;
use App\Application\PublicProjectionDelivery\PublicProjectionRoutedDeliveryMessageV1;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxRoutedWriterV1;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimOwnerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimState;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineRecord;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRecord;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryClassification;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;

final readonly class FakePublicProjectionOutboxWriter implements PublicProjectionOutboxRoutedWriterV1, PublicProjectionOutboxWriter
{
    public function __construct(private FakePublicProjectionOutboxState $state) {}

    public function append(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId): PublicProjectionOutboxWriteResult
    {
        $existing = $this->state->find($message, $consumerId);
        if ($existing !== null) {
            return $message->hasDivergentPayload($existing->message) ? PublicProjectionOutboxWriteResult::DivergentMessage : PublicProjectionOutboxWriteResult::AlreadyApplied;
        }
        $this->state->put(PublicProjectionOutboxRecord::pending($message, $consumerId));

        return PublicProjectionOutboxWriteResult::Applied;
    }

    public function appendRouted(
        PublicProjectionRoutedDeliveryMessageV1 $delivery,
        PublicProjectionOutboxConsumerId $consumerId,
    ): PublicProjectionOutboxWriteResult {
        if (! $delivery->hasValidRoutingProof()) {
            return PublicProjectionOutboxWriteResult::DivergentMessage;
        }
        $existing = $this->state->find($delivery->deliveryMessage, $consumerId);
        if ($existing !== null) {
            return $existing->routedDelivery == $delivery
                ? PublicProjectionOutboxWriteResult::AlreadyApplied
                : PublicProjectionOutboxWriteResult::DivergentMessage;
        }
        $this->state->put(PublicProjectionOutboxRecord::pendingRouted($delivery, $consumerId));

        return PublicProjectionOutboxWriteResult::Applied;
    }

    public function markDelivered(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId): PublicProjectionOutboxWriteResult
    {
        $record = $this->claimedBy($message, $consumerId, $ownerId);
        if (! $record instanceof PublicProjectionOutboxRecord) {
            return $record;
        }
        $this->state->put(new PublicProjectionOutboxRecord($message, $consumerId, PublicProjectionDeliveryStatus::Delivered, $record->attempts, PublicProjectionOutboxClaimState::Released, routedDelivery: $record->routedDelivery));

        return PublicProjectionOutboxWriteResult::Applied;
    }

    public function scheduleRetry(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId, PublicProjectionOutboxRetryDecision $decision): PublicProjectionOutboxWriteResult
    {
        $record = $this->claimedBy($message, $consumerId, $ownerId);
        if (! $record instanceof PublicProjectionOutboxRecord) {
            return $record;
        }
        $status = match ($decision->classification) {
            PublicProjectionOutboxRetryClassification::SourceNotReady => PublicProjectionDeliveryStatus::BlockedBySourceReadiness,
            PublicProjectionOutboxRetryClassification::SequenceGap => PublicProjectionDeliveryStatus::BlockedBySequenceGap,
            default => PublicProjectionDeliveryStatus::RetryScheduled,
        };
        $this->state->put(new PublicProjectionOutboxRecord($message, $consumerId, $status, $record->attempts, PublicProjectionOutboxClaimState::Released, retryDecision: $status === PublicProjectionDeliveryStatus::RetryScheduled ? $decision : null, routedDelivery: $record->routedDelivery));

        return PublicProjectionOutboxWriteResult::Applied;
    }

    public function quarantine(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, ?PublicProjectionOutboxClaimOwnerId $ownerId, PublicProjectionOutboxQuarantineDecision $decision): PublicProjectionOutboxWriteResult
    {
        $record = $this->state->find($message, $consumerId);
        if ($record === null) {
            return PublicProjectionOutboxWriteResult::NotFound;
        }
        if ($record->claimState === PublicProjectionOutboxClaimState::Claimed && $record->lease?->ownerId != $ownerId) {
            return PublicProjectionOutboxWriteResult::ClaimMismatch;
        }
        $quarantine = new PublicProjectionOutboxQuarantineRecord($message->messageId, $consumerId, $decision, $record->attempts);
        $this->state->put(new PublicProjectionOutboxRecord($message, $consumerId, PublicProjectionDeliveryStatus::Quarantined, $record->attempts, PublicProjectionOutboxClaimState::Abandoned, quarantine: $quarantine, routedDelivery: $record->routedDelivery));

        return PublicProjectionOutboxWriteResult::Applied;
    }

    public function releaseClaim(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId): PublicProjectionOutboxWriteResult
    {
        $record = $this->claimedBy($message, $consumerId, $ownerId);
        if (! $record instanceof PublicProjectionOutboxRecord) {
            return $record;
        }
        $this->state->put(new PublicProjectionOutboxRecord($message, $consumerId, PublicProjectionDeliveryStatus::Pending, $record->attempts, PublicProjectionOutboxClaimState::Released, routedDelivery: $record->routedDelivery));

        return PublicProjectionOutboxWriteResult::Applied;
    }

    private function claimedBy(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumer, PublicProjectionOutboxClaimOwnerId $owner): PublicProjectionOutboxRecord|PublicProjectionOutboxWriteResult
    {
        $record = $this->state->find($message, $consumer);
        if ($record === null) {
            return PublicProjectionOutboxWriteResult::NotFound;
        }

        return $record->claimState === PublicProjectionOutboxClaimState::Claimed && $record->lease?->ownerId == $owner ? $record : PublicProjectionOutboxWriteResult::ClaimMismatch;
    }
}
