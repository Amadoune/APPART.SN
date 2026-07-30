<?php

namespace App\Application\PublicProjectionRetry;

use InvalidArgumentException;

final readonly class PublicProjectionDeliveryTechnicalMetrics
{
    public function __construct(
        public int $retryCount,
        public int $retryLagSeconds,
        public int $quarantineSize,
        public int $oldestQuarantineAgeSeconds,
        public int $replayCount,
        public int $replayDurationMilliseconds,
        public int $blockedBySourceReadiness,
        public int $blockedBySequenceGap,
    ) {
        foreach (get_object_vars($this) as $value) {
            if ($value < 0) {
                throw new InvalidArgumentException('Technical delivery metrics cannot be negative.');
            }
        }
    }
}
