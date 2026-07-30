<?php

namespace App\Application\PublicProjectionOutbox\Contract;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRecord;

interface PublicProjectionOutboxReader
{
    /** @return list<PublicProjectionOutboxRecord> */
    public function findClaimable(PublicProjectionOutboxConsumerId $consumerId, int $limit): array;

    /** @return list<PublicProjectionOutboxRecord> */
    public function findBlocked(PublicProjectionOutboxConsumerId $consumerId): array;

    /** @return list<PublicProjectionOutboxRecord> */
    public function findRetryable(PublicProjectionOutboxConsumerId $consumerId): array;

    /** @return list<PublicProjectionOutboxRecord> */
    public function findQuarantined(PublicProjectionOutboxConsumerId $consumerId): array;

    /** @return list<PublicProjectionOutboxRecord> */
    public function findByAggregate(PublicProjectionOutboxConsumerId $consumerId, PublicProjectionDeliveryAggregateId $aggregateId): array;
}
