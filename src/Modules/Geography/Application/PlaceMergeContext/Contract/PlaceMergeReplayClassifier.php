<?php

namespace Appart\Modules\Geography\Application\PlaceMergeContext\Contract;

use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextInspectionResult;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeReplayOutcome;

interface PlaceMergeReplayClassifier
{
    public function classify(
        PlaceMergeContextV1 $requested,
        PlaceMergeContextInspectionResult $inspected,
    ): PlaceMergeReplayOutcome;
}
