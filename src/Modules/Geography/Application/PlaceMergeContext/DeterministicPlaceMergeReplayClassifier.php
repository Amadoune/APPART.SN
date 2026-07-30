<?php

namespace Appart\Modules\Geography\Application\PlaceMergeContext;

use Appart\Modules\Geography\Application\PlaceMergeContext\Contract\PlaceMergeReplayClassifier;

final readonly class DeterministicPlaceMergeReplayClassifier implements PlaceMergeReplayClassifier
{
    public function classify(
        PlaceMergeContextV1 $requested,
        PlaceMergeContextInspectionResult $inspected,
    ): PlaceMergeReplayOutcome {
        return match ($inspected->status) {
            PlaceMergeContextInspectionStatus::Missing => PlaceMergeReplayOutcome::InspectionMissing,
            PlaceMergeContextInspectionStatus::Corrupted => PlaceMergeReplayOutcome::InspectionCorrupted,
            PlaceMergeContextInspectionStatus::Found => $inspected->inspection?->context == $requested
                ? PlaceMergeReplayOutcome::AlreadyApplied
                : PlaceMergeReplayOutcome::ContextDivergence,
        };
    }
}
