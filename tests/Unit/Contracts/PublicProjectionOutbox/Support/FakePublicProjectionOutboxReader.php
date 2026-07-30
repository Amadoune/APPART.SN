<?php

namespace Tests\Unit\Contracts\PublicProjectionOutbox\Support;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryStatus;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxReader;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRecord;

final readonly class FakePublicProjectionOutboxReader implements PublicProjectionOutboxReader
{
    public function __construct(private FakePublicProjectionOutboxState $state) {}

    public function findClaimable(PublicProjectionOutboxConsumerId $consumerId, int $limit): array
    {
        return array_slice($this->filter($consumerId, [PublicProjectionDeliveryStatus::Pending, PublicProjectionDeliveryStatus::RetryScheduled, PublicProjectionDeliveryStatus::Claimed]), 0, $limit);
    }

    public function findBlocked(PublicProjectionOutboxConsumerId $consumerId): array
    {
        return $this->filter($consumerId, [PublicProjectionDeliveryStatus::BlockedBySequenceGap, PublicProjectionDeliveryStatus::BlockedBySourceReadiness]);
    }

    public function findRetryable(PublicProjectionOutboxConsumerId $consumerId): array
    {
        return $this->filter($consumerId, [PublicProjectionDeliveryStatus::RetryScheduled]);
    }

    public function findQuarantined(PublicProjectionOutboxConsumerId $consumerId): array
    {
        return $this->filter($consumerId, [PublicProjectionDeliveryStatus::Quarantined]);
    }

    public function findByAggregate(PublicProjectionOutboxConsumerId $consumerId, PublicProjectionDeliveryAggregateId $aggregateId): array
    {
        return array_values(array_filter($this->state->records, static fn (PublicProjectionOutboxRecord $record): bool => $record->consumerId == $consumerId && $record->message->aggregateId == $aggregateId));
    }

    /** @param list<PublicProjectionDeliveryStatus> $statuses
     * @return list<PublicProjectionOutboxRecord>
     */
    private function filter(PublicProjectionOutboxConsumerId $consumer, array $statuses): array
    {
        $records = array_values(array_filter($this->state->records, static fn (PublicProjectionOutboxRecord $record): bool => $record->consumerId == $consumer && in_array($record->status, $statuses, true)));
        usort($records, static fn (PublicProjectionOutboxRecord $left, PublicProjectionOutboxRecord $right): int => [$left->message->aggregateId->value, $left->message->order->aggregateVersion, $left->message->order->eventIndex->value] <=> [$right->message->aggregateId->value, $right->message->order->aggregateVersion, $right->message->order->eventIndex->value]);

        return $records;
    }
}
