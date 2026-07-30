<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\Contract;

use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationResult;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleTransitionRequest;

interface MediaItemLifecycleOrchestrator
{
    public function execute(MediaItemLifecycleTransitionRequest $request): MediaItemLifecycleOrchestrationResult;
}
