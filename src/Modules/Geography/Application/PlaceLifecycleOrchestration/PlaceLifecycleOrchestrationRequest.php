<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycleOrchestration;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleCurrentState;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;

final readonly class PlaceLifecycleOrchestrationRequest
{
    public function __construct(
        public PlaceLifecycleCurrentState $current,
        public PlaceLifecycleAction $action,
        public PlaceMergeContextV1 $context,
    ) {}
}
