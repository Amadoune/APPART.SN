<?php

namespace Tests\Unit\Contracts\PublicProjectionOutbox\Support;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryStatus;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxClaimManager;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimResult;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimState;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxLease;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRecord;
use DateTimeImmutable;

final readonly class FakePublicProjectionOutboxClaimManager implements PublicProjectionOutboxClaimManager
{
    public function __construct(private FakePublicProjectionOutboxState $state) {}

    public function claim(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxLease $lease): PublicProjectionOutboxClaimResult
    {
        $record = $this->state->find($message, $consumerId);
        if ($record === null || in_array($record->status, [PublicProjectionDeliveryStatus::Delivered, PublicProjectionDeliveryStatus::Quarantined], true)) {
            return PublicProjectionOutboxClaimResult::NothingToClaim;
        }
        if ($record->lease !== null && ! $record->lease->isExpiredAt($lease->claimedAt)) {
            return PublicProjectionOutboxClaimResult::AlreadyClaimed;
        }
        $result = $record->lease?->isExpiredAt($lease->claimedAt) === true ? PublicProjectionOutboxClaimResult::LeaseExpired : PublicProjectionOutboxClaimResult::Claimed;
        $this->state->put(new PublicProjectionOutboxRecord($message, $consumerId, PublicProjectionDeliveryStatus::Claimed, $record->attempts->incremented(), PublicProjectionOutboxClaimState::Claimed, $lease, routedDelivery: $record->routedDelivery));

        return $result;
    }

    public function expire(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, DateTimeImmutable $at): PublicProjectionOutboxClaimResult
    {
        $record = $this->state->find($message, $consumerId);
        if ($record?->lease === null || ! $record->lease->isExpiredAt($at)) {
            return PublicProjectionOutboxClaimResult::AlreadyClaimed;
        }
        $this->state->put(new PublicProjectionOutboxRecord($message, $consumerId, PublicProjectionDeliveryStatus::Pending, $record->attempts, PublicProjectionOutboxClaimState::Expired, routedDelivery: $record->routedDelivery));

        return PublicProjectionOutboxClaimResult::LeaseExpired;
    }

    public function abandon(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxLease $lease): PublicProjectionOutboxClaimResult
    {
        $record = $this->state->find($message, $consumerId);
        if ($record?->lease != $lease) {
            return PublicProjectionOutboxClaimResult::AlreadyClaimed;
        }
        $this->state->put(new PublicProjectionOutboxRecord($message, $consumerId, PublicProjectionDeliveryStatus::Pending, $record->attempts, PublicProjectionOutboxClaimState::Abandoned, routedDelivery: $record->routedDelivery));

        return PublicProjectionOutboxClaimResult::Abandoned;
    }
}
