<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationRequest;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationResult;

interface PropertyLifecycleOrchestrator
{
    public function transition(PropertyLifecycleOrchestrationRequest $request): PropertyLifecycleOrchestrationResult;
}
