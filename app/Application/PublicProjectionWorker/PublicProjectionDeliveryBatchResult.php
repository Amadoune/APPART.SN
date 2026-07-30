<?php

namespace App\Application\PublicProjectionWorker;

final readonly class PublicProjectionDeliveryBatchResult
{
    /** @param list<PublicProjectionDeliveryMessageOutcome> $outcomes */
    public function __construct(
        public int $requested,
        public int $claimed,
        public array $outcomes,
        public int $oldestMessageLagSeconds,
    ) {}

    public function count(PublicProjectionDeliveryOutcome $outcome): int
    {
        return count(array_filter($this->outcomes, static fn (PublicProjectionDeliveryMessageOutcome $item): bool => $item->outcome === $outcome));
    }
}
