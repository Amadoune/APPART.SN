<?php

namespace Appart\Modules\Geography\Application\PlaceMergeContext\Contract;

use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextInspectionResult;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeIntentId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;

interface PlaceMergeContextInspector
{
    public function inspect(PlaceId $sourceId, PlaceMergeIntentId $intentId): PlaceMergeContextInspectionResult;
}
