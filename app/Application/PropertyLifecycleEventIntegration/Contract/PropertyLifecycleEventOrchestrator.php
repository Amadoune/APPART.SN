<?php

namespace App\Application\PropertyLifecycleEventIntegration\Contract;

use App\Application\PropertyLifecycleEventIntegration\PropertyLifecycleEventOrchestrationRequest;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationResult;

interface PropertyLifecycleEventOrchestrator
{
    public function transition(PropertyLifecycleEventOrchestrationRequest $request): PropertyLifecycleOrchestrationResult;
}
