<?php

namespace App\Application\PublicProjectionReconciliation;

final readonly class PublicProjectionReconciliationDetector
{
    /** @return list<PublicProjectionDivergence> */
    public function detect(PublicProjectionReconciliationObservation $observation): array
    {
        $findings = [];
        if ($observation->messageMissing) {
            $findings[] = new PublicProjectionDivergence(PublicProjectionDivergenceType::MissingMessage, $observation);
        }
        if ($observation->sequenceGap) {
            $findings[] = new PublicProjectionDivergence(PublicProjectionDivergenceType::SequenceGap, $observation);
        }
        if ($observation->outboxHighWatermark != $observation->projectionHighWatermark) {
            $findings[] = new PublicProjectionDivergence(PublicProjectionDivergenceType::HighWatermarkDivergence, $observation);
        }
        if ($observation->blockedBySourceReadiness) {
            $findings[] = new PublicProjectionDivergence(PublicProjectionDivergenceType::BlockedBySourceReadiness, $observation);
        }
        if ($observation->blockedBySequenceGap) {
            $findings[] = new PublicProjectionDivergence(PublicProjectionDivergenceType::BlockedBySequenceGap, $observation);
        }

        return $findings;
    }
}
