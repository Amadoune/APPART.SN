<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycleOrchestration;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleDecision;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;

final readonly class PlaceLifecycleOrchestrationResult
{
    public function __construct(
        public PlaceLifecycleOrchestrationStatus $status,
        public PlaceLifecycleState $state,
        public ?PlaceLifecycleDecision $workflowDecision = null,
    ) {}
}
